<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$trajet_id = $_POST['trajet_id'] ?? null;

if (!$trajet_id) {
    echo "ID du trajet manquant.";
    exit;
}

// vérification du rôle de l'utilisateur
$stmt = $pdo->prepare("SELECT r.nom FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?");
$stmt->execute([$user_id]);
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!in_array("passager", $roles)) {
    echo "Vous devez être passager pour participer.";
    exit;
}

// vérification de la disponibilité des crédits
$stmt = $pdo->prepare("SELECT credits FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($user['credits'] < 1) {
    echo "Pas assez de crédits.";
    exit;
}

// vérifie l'existence du trajet et la disponibilité des places
$stmt = $pdo->prepare("SELECT * FROM trajets WHERE id = ? AND places_dispo > 0");
$stmt->execute([$trajet_id]);
$trajet = $stmt->fetch();

if (!$trajet) {
    echo "Trajet introuvable ou plus de places.";
    exit;
}

// ajoute une entrée dans la table des réservations avec statut = 'confirmee'
$stmt = $pdo->prepare("INSERT INTO reservations (trajet_id, passager_id, statut) VALUES (?, ?, 'confirmee')");
$stmt->execute([$trajet_id, $user_id]);

// met à jour le nombre de places disponibles
$stmt = $pdo->prepare("UPDATE trajets SET places_dispo = places_dispo - 1 WHERE id = ?");
$stmt->execute([$trajet_id]);

// on récupère le prix du trajet et l'ID du conducteur
$stmt = $pdo->prepare("SELECT prix, conducteur_id FROM trajets WHERE id = ?");
$stmt->execute([$trajet_id]);
$trajet = $stmt->fetch();

$prix = (int) $trajet['prix'];
$conducteur_id = $trajet['conducteur_id'];

// vérification : le passager a-t-il assez de crédits
if ($user['credits'] < $prix) {
    echo "Pas assez de crédits pour ce trajet.";
    exit;
}

// déduction des crédits du passager
$stmt = $pdo->prepare("UPDATE users SET credits = credits - ? WHERE id = ?");
$stmt->execute([$prix, $user_id]);

// ajout au conducteur : montant total moins 2 crédits
$credits_pour_conducteur = max(0, $prix - 2);
$stmt = $pdo->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
$stmt->execute([$credits_pour_conducteur, $conducteur_id]);

// la plateforme (id = 1) reçoit 2 crédits
$stmt = $pdo->prepare("UPDATE users SET credits = credits + 2 WHERE id = 1");
$stmt->execute();

// définir le message
$_SESSION['flash'] = "Réservation confirmée ! $prix crédit" . ($prix > 1 ? "s" : "") . " ont été débités de votre compte.";

// redirige vers la page de confirmation
header("Location: confirmation.php?success=1");
exit;
?>

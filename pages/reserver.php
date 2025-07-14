<?php
session_start();
require_once '../config/db.php';

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

// vérifie l'existence du trajet et la disponibilité des places
$stmt = $pdo->prepare("SELECT * FROM trajets WHERE id = ? AND places_dispo > 0");
$stmt->execute([$trajet_id]);
$trajet = $stmt->fetch();

if (!$trajet) {
    echo "Trajet introuvable ou plus de places.";
    exit;
}

// ajoute une entrée dans la table des réservations (en supposant qu'elle existe déjà)
$stmt = $pdo->prepare("INSERT INTO reservations (trajet_id, passager_id) VALUES (?, ?)");
$stmt->execute([$trajet_id, $user_id]);

// met à jour le nombre de places disponibles
$stmt = $pdo->prepare("UPDATE trajets SET places_dispo = places_dispo - 1 WHERE id = ?");
$stmt->execute([$trajet_id]);

// redirige vers la page de confirmation
header("Location: confirmation.php?success=1");
exit;
?>

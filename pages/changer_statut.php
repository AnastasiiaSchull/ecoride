<?php
session_start();
require_once '../config/db.php';

// vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../connexion.php");
    exit;
}

$trajet_id = $_POST['trajet_id'] ?? null;
$nouveau_statut = $_POST['statut'] ?? null;

// liste des statuts autorisés
$statuts_valides = ['en_cours', 'terminé'];
if (!$trajet_id || !in_array($nouveau_statut, $statuts_valides)) {
    exit("Statut invalide.");
}

// sécurité : vérifier que le trajet appartient au conducteur connecté
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT conducteur_id FROM trajets WHERE id = ?");
$stmt->execute([$trajet_id]);
$trajet = $stmt->fetch();

if (!$trajet || $trajet['conducteur_id'] != $user_id) {
    exit("Action non autorisée.");
}

// met à jour le statut du trajet
$stmt = $pdo->prepare("UPDATE trajets SET statut = ? WHERE id = ?");
$stmt->execute([$nouveau_statut, $trajet_id]);

//  bonus : Si on termine le trajet, on crédite le conducteur
if ($nouveau_statut === 'terminé') {
    // compter le nombre de réservations confirmées sur ce trajet
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE trajet_id = ? AND statut = 'confirmee'");
    $stmt->execute([$trajet_id]);
    $credits_a_ajouter = (int) $stmt->fetchColumn();

    // ajoute les crédits au conducteur
    if ($credits_a_ajouter > 0) {
        $stmt = $pdo->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
        $stmt->execute([$credits_a_ajouter, $user_id]);
    }
}

$_SESSION['flash'] = "Statut du trajet mis à jour avec succès.";
header("Location: mes_trajets.php");
exit;

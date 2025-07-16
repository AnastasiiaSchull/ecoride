<?php
session_start();
require_once '../config/db.php';

// vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// validation simple
$ville_depart = trim($_POST['ville_depart'] ?? '');
$ville_arrivee = trim($_POST['ville_arrivee'] ?? '');
$date_depart = $_POST['date_depart'] ?? '';
$prix = $_POST['prix'] ?? '';
$vehicule_id = $_POST['vehicule_id'] ?? '';

$errors = [];

// validation
if ($ville_depart === '' || $ville_arrivee === '') {
    $errors[] = "Les villes ne peuvent pas être vides.";
}
if ($date_depart === '') {
    $errors[] = "La date de départ est requise.";
}
if (!is_numeric($prix) || $prix <= 0) {
    $errors[] = "Le prix doit être un nombre positif.";
}
if (!is_numeric($vehicule_id)) {
    $errors[] = "Véhicule invalide.";
}

if (!empty($errors)) {
    echo "Erreur : <br>";
    foreach ($errors as $e) {
        echo "- " . htmlspecialchars($e) . "<br>";
    }
    exit;
}

// récupérer le type d'énergie du véhicule pour définir si éco
$stmt = $pdo->prepare("SELECT energie FROM vehicules WHERE id = ?");
$stmt->execute([$vehicule_id]);
$energie = $stmt->fetchColumn();

$eco = ($energie === 'electrique') ? 1 : 0;


// insérer le trajet
$stmt = $pdo->prepare("
    INSERT INTO trajets (ville_depart, ville_arrivee, date_depart, prix, conducteur_id, vehicule_id, statut, eco)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([$ville_depart, $ville_arrivee, $date_depart, $prix, $user_id, $vehicule_id, 'à_venir', $eco]);

$trajet_id = $pdo->lastInsertId();

// récupération de l'id du trajet inséré
$trajet_id = $pdo->lastInsertId();

// insérer les préférences (si sélectionnées)
$stmt = $pdo->prepare("
  SELECT p.nom FROM preferences p
  JOIN vehicule_preferences vp ON p.id = vp.preference_id
  WHERE vp.vehicule_id = ?
");
$stmt->execute([$vehicule_id]);
$preferences = $stmt->fetchAll(PDO::FETCH_COLUMN);

// redirection vers mes trajets
header("Location: mes_trajets.php");
exit;

<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


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
$date_arrivee = $_POST['date_arrivee'] ?? '';
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

// Récupère les infos du véhicule
$stmt = $pdo->prepare("SELECT energie, places FROM vehicules WHERE id = ?");
$stmt->execute([$vehicule_id]);
$vehicule = $stmt->fetch();

if (!$vehicule) {
    echo "Véhicule introuvable.";
    exit;
}

$eco = ($vehicule['energie'] === 'electrique') ? 1 : 0;
$places_dispo = (int)$vehicule['places'];
// récupérer le type d'énergie du véhicule pour définir si éco
// $stmt = $pdo->prepare("SELECT energie FROM vehicules WHERE id = ?");
// $stmt->execute([$vehicule_id]);
// $energie = $stmt->fetchColumn();

// $eco = ($energie === 'electrique') ? 1 : 0;


// Insère le trajet
$stmt = $pdo->prepare("
    INSERT INTO trajets (conducteur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, date_arrivee, prix, places_dispo, eco, statut)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $user_id,
    $vehicule_id,
    $ville_depart,
    $ville_arrivee,
    $date_depart,
    $date_arrivee,
    $prix,
    $places_dispo,
    $eco,
    'à_venir'
]);


// récupération de l'id du trajet inséré
$trajet_id = $pdo->lastInsertId();

// echo "<pre>";
// echo "DATE DEPART : $date_depart\n";
// echo "DATE ARRIVEE : $date_arrivee\n";
// echo "</pre>";

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

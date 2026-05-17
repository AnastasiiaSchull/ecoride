<?php
require_once __DIR__ . '/../../config/db.php';


$ville_depart = $_GET['ville_depart'] ?? null;
$ville_arrivee = $_GET['ville_arrivee'] ?? null;
$date = $_GET['date'] ?? null;

if (!$ville_depart || !$ville_arrivee || !$date) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètre manquant']);
    exit;
}

//On cherche le nombre maximum de places pour une direction et une date données
$sql = "SELECT MAX(places_dispo) FROM trajets 
        WHERE ville_depart = ? AND ville_arrivee = ? 
          AND DATE(date_depart) = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$ville_depart, $ville_arrivee, $date]);
$maxPlaces = $stmt->fetchColumn();

echo json_encode([
    'places_max' => $maxPlaces ? (int)$maxPlaces : 0
]);

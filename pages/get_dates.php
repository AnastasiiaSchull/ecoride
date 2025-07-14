<?php
require_once '../config/db.php';

$type = $_GET['type'] ?? null;
$ville = $_GET['ville'] ?? null;
$other = $_GET['other'] ?? null;

if (!$type || !$ville || !$other) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètre manquant']);
    exit;
}

if ($type === 'depart') {
  // ищем где ville_depart = $ville и ville_arrivee = $other
  $sql = "SELECT DISTINCT DATE(date_depart) as date_only 
          FROM trajets 
          WHERE ville_depart = ? AND ville_arrivee = ?";
} else {
  // ищем где ville_arrivee = $ville и ville_depart = $other
  $sql = "SELECT DISTINCT DATE(date_depart) as date_only 
          FROM trajets 
          WHERE ville_arrivee = ? AND ville_depart = ?";
}

$stmt = $pdo->prepare($sql);
$stmt->execute([$ville, $other]);
$dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($dates);

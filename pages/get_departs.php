<?php
require_once '../config/db.php';

if (isset($_GET['destination']) && !empty($_GET['destination'])) {
    $destination = $_GET['destination'];

    $stmt = $pdo->prepare("SELECT DISTINCT ville_depart FROM trajets WHERE ville_arrivee = ?");
    $stmt->execute([$destination]);
    $departs = $stmt->fetchAll(PDO::FETCH_COLUMN);

    header('Content-Type: application/json');
    echo json_encode($departs);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Paramètre manquant']);

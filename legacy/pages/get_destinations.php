<?php
require_once __DIR__ . '/../../config/db.php';


if (isset($_GET['depart']) && !empty($_GET['depart'])) {
    $depart = $_GET['depart'];
    $stmt = $pdo->prepare("SELECT DISTINCT ville_arrivee FROM trajets WHERE ville_depart = ?");
    $stmt->execute([$depart]);
    $destinations = $stmt->fetchAll(PDO::FETCH_COLUMN);

    header('Content-Type: application/json');
    echo json_encode($destinations);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Paramètre manquant']);
?>
<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


// vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $targetDir = '/assets/uploads/';
    $fileName = basename($_FILES['photo']['name']);
    $filePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // vérifie que c'est une image
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileType, $allowedTypes)) {
        echo "Type de fichier non autorisé.";
        exit;
    }

    // déplace l'image
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $filePath)) {
        // sauvegarde dans la BDD
        $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE id = ?");
        $stmt->execute([$fileName, $userId]);
        header("Location: mon_espace.php"); 
        exit;
    } else {
        echo "Erreur lors de l'upload.";
    }
}

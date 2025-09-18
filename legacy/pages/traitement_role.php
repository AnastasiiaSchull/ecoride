<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


// vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    $role = $_POST['role'];
    $user_id = $_SESSION['user_id'];

    // met à jour le rôle dans la base de données
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$role, $user_id]);

    // optionnel : mettre à jour la variable session aussi
    $_SESSION['user']['role'] = $role;

    // redirige vers mon_espace.php après modification
    header("Location: mon_espace.php");
    exit;
} else {
    echo "Une erreur est survenue. Veuillez réessayer.";
}

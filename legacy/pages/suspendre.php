<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


// protection : uniquement pour l'administrateur
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: connexion.php");
    exit;
}

$stmt = $pdo->prepare("SELECT r.nom FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?");
$stmt->execute([$user_id]);
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('admin', $roles)) {
    echo "Accès refusé.";
    exit;
}

$id = $_GET['id'] ?? null;

// empêcher la désactivation de soi-même
if ($id == $user_id) {
    echo "Vous ne pouvez pas vous suspendre vous-même.";
    exit;
}

// mettre à jour le statut
$stmt = $pdo->prepare("UPDATE users SET is_active = FALSE WHERE id = ?");
$stmt->execute([$id]);

header("Location: admin.php");
exit;

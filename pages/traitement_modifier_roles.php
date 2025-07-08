<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: connexion.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$selected_roles = $_POST['roles'] ?? [];

// удалить старые роли
$stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
$stmt->execute([$user_id]);

// вставить новые роли
foreach ($selected_roles as $role_name) {
  $stmt = $pdo->prepare("SELECT id FROM roles WHERE nom = ?");
  $stmt->execute([$role_name]);
  $role_id = $stmt->fetchColumn();

  if ($role_id) {
    $insert = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
    $insert->execute([$user_id, $role_id]);
  }
}

header("Location: mon_espace.php");
exit;

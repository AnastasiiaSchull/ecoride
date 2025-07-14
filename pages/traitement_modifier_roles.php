<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: connexion.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$selected_roles = $_POST['roles'] ?? [];

// supprimer les anciens rôles
$stmt = $pdo->prepare("DELETE FROM user_roles WHERE user_id = ?");
$stmt->execute([$user_id]);

// insérer les nouveaux rôles
foreach ($selected_roles as $role_name) {
  $stmt = $pdo->prepare("SELECT id FROM roles WHERE nom = ?");
  $stmt->execute([$role_name]);
  $role_id = $stmt->fetchColumn();

  if ($role_id) {
    $insert = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
    $insert->execute([$user_id, $role_id]);
  }
}

// si le rôle "passager" est parmi les rôles sélectionnés, on ajoute des crédits
if (in_array('passager', $selected_roles)) {
  $stmt = $pdo->prepare("SELECT credits FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $currentCredits = $stmt->fetchColumn();

  if ($currentCredits == 0) {
    $stmt = $pdo->prepare("UPDATE users SET credits = 5 WHERE id = ?");
    $stmt->execute([$user_id]);
  }
}

header("Location: mon_espace.php");
exit;

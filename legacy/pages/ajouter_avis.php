<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


if (!isset($_SESSION['user_id'])) {
  header("Location: connexion.php");
  exit;
}

$passager_id = $_SESSION['user_id'];
$conducteur_id = intval($_POST['conducteur_id']);
$note = intval($_POST['note']);
$commentaire = trim($_POST['commentaire']);

if ($conducteur_id && $note && !empty($commentaire)) {
  $stmt = $pdo->prepare("INSERT INTO avis (conducteur_id, passager_id, note, commentaire, approuve, is_problem)
                         VALUES (?, ?, ?, ?, NULL, 0)");
  $stmt->execute([$conducteur_id, $passager_id, $note, $commentaire]);

  header("Location: mon_espace.php?success=avis");
  exit;
} else {
  header("Location: mon_espace.php?error=champ");
  exit;
}

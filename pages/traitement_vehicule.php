<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: connexion.php");
  exit;
}

$user_id = $_SESSION['user_id'];

$marque = $_POST['marque'] ?? '';
$modele = $_POST['modele'] ?? '';
$couleur = $_POST['couleur'] ?? '';
$energie = $_POST['energie'] ?? '';
$places = $_POST['places'] ?? 0;
$preferences = $_POST['preferences'] ?? [];

$errors = [];

if (empty($marque) || empty($modele) || empty($couleur) || empty($energie) || $places <= 0) {
  $errors[] = "Tous les champs du véhicule doivent être correctement remplis.";
}

if (!empty($errors)) {
  // здесь можно сохранить $errors в сессию и показать пользователю
  foreach ($errors as $e) {
    echo "<p style='color:red'>" . htmlspecialchars($e) . "</p>";
  }
  exit;
}

// валидация
if ($marque && $modele && $couleur && $energie && $places > 0) {
  $stmt = $pdo->prepare("INSERT INTO vehicules (user_id, marque, modele, couleur, energie, places) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([$user_id, $marque, $modele, $couleur, $energie, $places]);

}

$vehicule_id = $pdo->lastInsertId();
// 2. Вставка préférences (если есть)
if (!empty($preferences)) {
  $stmtPref = $pdo->prepare("INSERT INTO vehicule_preferences (vehicule_id, preference_id) VALUES (?, ?)");

  foreach ($preferences as $prefId) {
    if (is_numeric($prefId)) {
      $stmtPref->execute([$vehicule_id, $prefId]);
    }
  }
}

header("Location: mon_espace.php");
exit;

?>
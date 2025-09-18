<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


if (!isset($_SESSION['user_id'])) {
  header("Location: connexion.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// On récupère tous les rôles de l'utilisateur actuel
$stmt = $pdo->prepare("SELECT r.nom FROM roles r
                       JOIN user_roles ur ON r.id = ur.role_id
                       WHERE ur.user_id = ?");
$stmt->execute([$user_id]);
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier mes rôles</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<?php include_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container" style="margin-top: 2rem;">
  <h2>Modifier mes rôles</h2>

  <form method="POST" action="traitement_modifier_roles.php">
    <label><input type="checkbox" name="roles[]" value="conducteur" <?= in_array('conducteur', $roles) ? 'checked' : '' ?>> Conducteur</label><br>
    <label><input type="checkbox" name="roles[]" value="passager" <?= in_array('passager', $roles) ? 'checked' : '' ?>> Passager</label><br><br>
    <button type="submit" class="btn">Enregistrer</button>
  </form>
</main>

<?php include_once __DIR__ . '/../../includes/footer.php';?>

</body>
</html>

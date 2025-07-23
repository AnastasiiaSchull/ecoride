<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


if (!isset($_SESSION['user_id'])) {
  header("Location: connexion.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// récupérer les véhicules du conducteur
$stmt = $pdo->prepare("SELECT * FROM vehicules WHERE user_id = ?");
$stmt->execute([$user_id]);
$vehicules = $stmt->fetchAll();

// récupérer toutes les préférences
$stmt = $pdo->query("SELECT * FROM preferences");
$preferences = $stmt->fetchAll();

// vérifier si l'utilisateur est conducteur
$stmt = $pdo->prepare("SELECT r.nom FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?");
$stmt->execute([$user_id]);
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!in_array("conducteur", $roles)) {
  echo "Accès réservé aux conducteurs.";
  exit;
}

$date_arrivee = $_POST['date_arrivee'] ?? '';

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Créer un trajet</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>

<body>

  <?php include_once __DIR__ . '/../../includes/header.php'; ?>

  <main class="container" style="margin-top: 2rem;">
    <h2>Créer un nouveau trajet</h2>

    <form method="POST" action="traitement_trajet.php">
      <label>Ville de départ :
        <input type="text" name="ville_depart" required>
      </label><br>

      <label>Ville d’arrivée :
        <input type="text" name="ville_arrivee" required>
      </label><br>
       <label>Date et heure de départ :
        <input type="datetime-local" name="date_depart" required>
      </label><br>

      <label>Date et heure d’arrivée :
        <input type="datetime-local" name="date_arrivee" required>
      </label><br>

      <label>Prix par place (€) :
        <input type="number" name="prix" min="1" required>
      </label><br>

      <label>Véhicule :
        <select name="vehicule_id" required>
          <?php foreach ($vehicules as $v): ?>
            <option value="<?= $v['id'] ?>">
              <?= htmlspecialchars($v['marque'] . ' ' . $v['modele']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label><br>

      <label>Préférences :</label><br>
      <?php foreach ($preferences as $pref): ?>
        <input type="checkbox" name="preferences[]" value="<?= $pref['id'] ?>">
        <?= htmlspecialchars($pref['nom']) ?><br>
      <?php endforeach; ?>

      <button type="submit" class="btn" style="margin-top: 1rem;">Publier le trajet</button>
    </form>
  </main>

  <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>
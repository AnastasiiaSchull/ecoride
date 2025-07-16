<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: connexion.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// récupère les trajets du conducteur
$stmt = $pdo->prepare("SELECT * FROM trajets WHERE conducteur_id = ?");
$stmt->execute([$user_id]);
$mes_trajets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes trajets - EcoRide</title>
  <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container" style="margin-top: 2rem;">
  <h2>Mes trajets</h2>

  <?php if (count($mes_trajets) > 0): ?>
    <ul>
      <?php foreach ($mes_trajets as $trajet): ?>
        <li style="margin-bottom: 1rem;">
          <strong><?= htmlspecialchars($trajet['ville_depart']) ?> → <?= htmlspecialchars($trajet['ville_arrivee']) ?></strong><br>
          Date départ : <?= date('d/m/Y H:i', strtotime($trajet['date_depart'])) ?><br>
          Statut : <strong><?= $trajet['statut'] ?></strong><br>

          <?php if ($trajet['statut'] === 'à_venir'): ?>
            <form action="changer_statut.php" method="POST" style="display:inline;">
              <input type="hidden" name="trajet_id" value="<?= $trajet['id'] ?>">
              <input type="hidden" name="statut" value="en_cours">
              <button type="submit" class="btn">Démarrer</button>
            </form>
          <?php elseif ($trajet['statut'] === 'en_cours'): ?>
            <form action="changer_statut.php" method="POST" style="display:inline;">
              <input type="hidden" name="trajet_id" value="<?= $trajet['id'] ?>">
              <input type="hidden" name="statut" value="terminé">
              <button type="submit" class="btn">Terminer</button>
            </form>
          <?php else: ?>
            <span style="color: green;">✔ Terminé</span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>Vous n'avez pas encore publié de trajet.</p>
  <?php endif; ?>

</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>

<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


if (!isset($_SESSION['user_id'])) {
  header("Location: connexion.php");
  exit;
}

$user_id = $_SESSION['user_id'];

// récupérer toutes les réservations de cet utilisateur + informations sur les trajets
$stmt = $pdo->prepare("
  SELECT t.*, r.statut AS statut_reservation, r.date_reservation
  FROM reservations r
  JOIN trajets t ON r.trajet_id = t.id
  WHERE r.passager_id = ?
  ORDER BY r.date_reservation DESC
");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes réservations - EcoRide</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<?php include_once __DIR__ . '/../../includes/header.php'; ?>

<main class="container" style="margin-top: 2rem;">
  <h2>Mes réservations</h2>

  <?php if (count($reservations) > 0): ?>
    <ul>
      <?php foreach ($reservations as $r): ?>
        <li style="margin-bottom: 1rem;">
          <strong><?= htmlspecialchars($r['ville_depart']) ?> → <?= htmlspecialchars($r['ville_arrivee']) ?></strong><br>
          Départ : <?= date('d/m/Y H:i', strtotime($r['date_depart'])) ?><br>
          Arrivée : <?= date('d/m/Y H:i', strtotime($r['date_arrivee'])) ?><br>
          Statut du trajet : <strong><?= htmlspecialchars($r['statut']) ?></strong><br>
          Statut de votre réservation : <strong><?= htmlspecialchars($r['statut_reservation']) ?></strong><br>
          Réservé le : <?= date('d/m/Y H:i', strtotime($r['date_reservation'])) ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>Vous n'avez encore réservé aucun trajet.</p>
  <?php endif; ?>
</main>

 <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>

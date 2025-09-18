<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes réservations</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include dirname(__DIR__,3).'/includes/header.php'; ?>
<main class="container">
  <h2>Mes réservations</h2>

  <?php if (!$reservations): ?>
    <p>Aucune réservation.</p>
  <?php else: ?>
    <ul>
  <?php foreach ($reservations as $r): ?>
    <li>
      <?= htmlspecialchars($r['ville_depart']) ?> → <?= htmlspecialchars($r['ville_arrivee']) ?>
      (<?= date('d/m/Y H:i', strtotime($r['date_depart'])) ?>) —
      statut : <?= htmlspecialchars($r['statut']) ?>

      <?php // bouton pour écrire un avis si éligible ?>
      <?php if ((int)($r['peut_avis'] ?? 0) === 1): ?>
        <a class="btn" href="/avis/nouveau?reservation_id=<?= (int)$r['id'] ?>">
          Écrire un avis
        </a>
      <?php endif; ?>
    </li>
  <?php endforeach; ?>
</ul>

  <?php endif; ?>
</main>
<?php include dirname(__DIR__,3).'/includes/footer.php'; ?>
</body>
</html>

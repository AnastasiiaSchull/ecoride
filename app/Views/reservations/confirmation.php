<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="UTF-8">
<title>Confirmation</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head><body>
<?php include dirname(__DIR__,3).'/includes/header.php'; ?>
<main class="container">
  <h2 style="color:green;">✔ Réservation confirmée !</h2>
  <?php if (!empty($_SESSION['flash'])): ?>
    <p><?= htmlspecialchars($_SESSION['flash']) ?></p>
    <?php unset($_SESSION['flash']); ?>
  <?php endif; ?>
  <a class="btn" href="/mon_espace">Mon espace</a>
</main>
<?php include dirname(__DIR__,3).'/includes/footer.php'; ?>
</body></html>

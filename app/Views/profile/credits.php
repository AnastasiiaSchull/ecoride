<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="UTF-8"><title>Ajouter des crédits</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head><body>
<?php include ROOT.'/includes/header.php'; ?>
<main class="container" style="margin-top:2rem;">
  <h2>Ajouter des crédits</h2>

  <?php if (!empty($message)): ?>
    <div class="alert-success"><?= htmlspecialchars($message) ?></div>
  <?php elseif (!empty($_SESSION['flash'])): ?>
    <div class="alert-success"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
  <?php endif; ?>

  <form method="POST" action="/credits">
    <label>Montant à ajouter :
      <input type="number" name="montant" min="1" required>
    </label>
    <button type="submit" class="btn">Valider</button>
  </form>

  <p style="margin-top:1rem;"><a href="/mon_espace">Retour à mon espace</a></p>
</main>
<?php include ROOT.'/includes/footer.php'; ?>
</body></html>

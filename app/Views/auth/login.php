<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Connexion - EcoRide</title>
  <link rel="stylesheet" href="/assets/css/style.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
  <?php include ROOT . '/includes/header.php'; ?>

  <main>
    <h2 class="search" style="margin-top: 1.125rem">Connexion</h2>

    <div class="container" style="margin-top: 1.25rem">
      <?php if (!empty($errors)): ?>
        <div class="warning">
          <?= htmlspecialchars(implode(' ', $errors)) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/connexion">
        <label for="email">Email :
          <input type="email" id="email" name="email"
                 value="<?= htmlspecialchars($old['email'] ?? '') ?>" required />
        </label>

        <label for="password">Mot de passe :
          <input type="password" id="password" name="password" required />
        </label>

        <div style="text-align: center; margin-top: 1rem;">
          <button type="submit" class="btn">Se connecter</button>
        </div>
      </form>

      <div class="div-centre">
        <p style="margin-top: 1rem;">Pas encore inscrit ?
          <a href="/inscription" style="margin-left: 1.25rem;">Créer un compte</a>
        </p>
      </div>
    </div>
  </main>

  <?php include ROOT . '/includes/footer.php'; ?>
</body>
</html>

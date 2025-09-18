<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier mes rôles</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include ROOT.'/includes/header.php'; ?>

<main class="container" style="margin-top:2rem;">
  <h2>Modifier mes rôles</h2>

  <form method="POST" action="/roles">
    <?php foreach ($all as $role): 
      $checked = in_array($role['nom'], $current, true) ? 'checked' : '';
    ?>
      <label style="display:block; margin: .5rem 0;">
        <input type="checkbox" name="roles[]" value="<?= htmlspecialchars($role['nom']) ?>" <?= $checked ?>>
        <?= htmlspecialchars($role['label']) ?>
      </label>
    <?php endforeach; ?>

    <button type="submit" class="btn" style="margin-top:1rem;">Enregistrer</button>
  </form>
</main>

<?php include ROOT.'/includes/footer.php'; ?>
</body>
</html>

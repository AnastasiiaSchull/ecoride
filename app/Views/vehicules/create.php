<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Ajouter un véhicule</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include_once dirname(__DIR__,3).'/includes/header.php'; ?>

<main class="container" style="margin-top:2rem;">
  <h2>Ajouter un véhicule</h2>

  <?php if (!empty($errors)): ?>
    <div class="errors">
      <ul>
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="/vehicules/creer">
    <label>Marque :
      <input type="text" name="marque" required
             value="<?= htmlspecialchars($old['marque'] ?? '') ?>">
    </label><br>

    <label>Modèle :
      <input type="text" name="modele" required
             value="<?= htmlspecialchars($old['modele'] ?? '') ?>">
    </label><br>

    <label>Couleur :
      <input type="text" name="couleur" required
             value="<?= htmlspecialchars($old['couleur'] ?? '') ?>">
    </label><br>

    <label>Énergie :
      <select name="energie" required>
        <?php
          $ener = $old['energie'] ?? '';
          $opts = ['essence'=>'Essence','diesel'=>'Diesel','electrique'=>'Électrique'];
          foreach ($opts as $val=>$label) {
              $sel = ($ener === $val) ? 'selected' : '';
              echo "<option value=\"$val\" $sel>$label</option>";
          }
        ?>
      </select>
    </label><br>

    <label>Places disponibles :
      <input type="number" name="places" min="1" required
             value="<?= htmlspecialchars($old['places'] ?? '1') ?>">
    </label><br>

    <label>Préférences :</label><br>
    <?php foreach ($preferences as $p): ?>
      <?php
        $checked = (isset($old['preferences']) && in_array($p['id'], (array)$old['preferences'])) ? 'checked' : '';
      ?>
      <label style="display:block;">
        <input type="checkbox" name="preferences[]" value="<?= (int)$p['id'] ?>" <?= $checked ?>>
        <?= htmlspecialchars(ucfirst($p['nom'])) ?>
      </label>
    <?php endforeach; ?>

    <button type="submit" class="btn" style="margin-top:1rem;">Valider</button>
    <a href="/mon_espace" class="btn" style="margin-left:.5rem;">Annuler</a>
  </form>
</main>

<?php include_once dirname(__DIR__,3).'/includes/footer.php'; ?>
</body>
</html>

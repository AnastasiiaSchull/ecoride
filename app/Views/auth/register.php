<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Inscription</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<?php include_once dirname(__DIR__,3) . '/includes/header.php'; ?>

<main>
  <h2 class="search" style="margin-top: 1.125rem">Création de compte</h2>
  <div class="container" style="margin-top: 1.25rem">

    <?php if (!empty($errors)): ?>
      <div class="errors">
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="/inscription">
      <label class="label-inscription">Pseudo :
        <input type="text" name="pseudo" required value="<?= htmlspecialchars($old['pseudo'] ?? '') ?>">
      </label>

      <label class="label-inscription">Email :
        <input type="email" name="email" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
      </label>

      <label class="label-inscription">Mot de passe :
        <input type="password" name="password" required>
      </label>

      <label class="label-inscription-last">Confirmer mot de passe :
        <input type="password" name="confirm_password" required>
      </label>

      <?php
        $oldRoles = $old['roles'] ?? [];
        $isConducteur = in_array('conducteur', $oldRoles, true);
      ?>
      <div class="checkbox">
        <label><input type="checkbox" name="roles[]" value="conducteur"
          <?= $isConducteur ? 'checked' : '' ?>
          onclick="toggleCarFields()"> Conducteur</label>
        <label><input type="checkbox" name="roles[]" value="passager"
          <?= in_array('passager', $oldRoles, true) ? 'checked' : '' ?>
          onclick="toggleCarFields()"> Passager</label>
      </div>

      <div id="carFields" style="display: <?= $isConducteur ? 'block' : 'none' ?>;">
        <label>Marque de voiture :
          <input type="text" name="marque" value="<?= htmlspecialchars($old['marque'] ?? '') ?>">
        </label>
        <label>Modèle :
          <input type="text" name="modele" value="<?= htmlspecialchars($old['modele'] ?? '') ?>">
        </label>
        <label>Couleur :
          <input type="text" name="couleur" value="<?= htmlspecialchars($old['couleur'] ?? '') ?>">
        </label>
        <label>Type de carburant :
          <select name="energie">
            <option value="">-- Choisissez --</option>
            <?php $ener = $old['energie'] ?? ''; ?>
            <option value="essence"    <?= $ener==='essence'?'selected':''; ?>>Essence</option>
            <option value="diesel"     <?= $ener==='diesel'?'selected':''; ?>>Diesel</option>
            <option value="electrique" <?= $ener==='electrique'?'selected':''; ?>>Électrique</option>
          </select>
        </label>
        <label>Nombre de places :
          <input type="number" name="places" min="1" value="<?= htmlspecialchars($old['places'] ?? '') ?>">
        </label>
      </div>

      <div style="text-align: center; margin-top: 1rem;">
        <button type="submit" class="btn">Créer un compte</button>
      </div>
    </form>

    <br><hr>
    <div class="connexion-lien">
      <span>Déjà inscrit ?</span>
      <a href="/connexion" class="btn-lien">Se connecter</a>
    </div>
    <br>
  </div>
</main>

<?php include_once dirname(__DIR__,3) . '/includes/footer.php'; ?>

<script>
  function toggleCarFields() {
    const checked = [...document.querySelectorAll('input[name="roles[]"]:checked')].map(x=>x.value);
    document.getElementById('carFields').style.display = checked.includes('conducteur') ? 'block' : 'none';
  }
  window.addEventListener('DOMContentLoaded', toggleCarFields);
</script>
</body>
</html>

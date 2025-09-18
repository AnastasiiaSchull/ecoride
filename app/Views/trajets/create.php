<!DOCTYPE html>
<html lang="fr"><head>
<meta charset="UTF-8">
<title>Créer un trajet</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head><body>
<?php include ROOT.'/includes/header.php'; ?>
<main class="container" style="margin-top:2rem;">
  <h2>Créer un nouveau trajet</h2>

  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert-error"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
  <?php endif; ?>

  <form method="POST" action="/trajets">
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
          <option value="<?= (int)$v['id'] ?>"><?= htmlspecialchars($v['marque'].' '.$v['modele']) ?></option>
        <?php endforeach; ?>
      </select>
    </label><br>

    <button class="btn" type="submit" style="margin-top:1rem;">Publier le trajet</button>
  </form>
</main>
<?php include ROOT.'/includes/footer.php'; ?>
</body></html>

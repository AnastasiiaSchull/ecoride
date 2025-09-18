<!DOCTYPE html><html lang="fr"><head>
<meta charset="UTF-8"><title>Mes trajets</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head><body>
<?php include ROOT.'/includes/header.php'; ?>
<main class="container" style="margin-top:2rem;">
  <h2>Mes trajets</h2>

  <?php if (!empty($_SESSION['flash'])): ?>
    <div class="alert-success"><?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
  <?php endif; ?>

  <?php if ($mes_trajets): ?>
    <ul>
      <?php foreach ($mes_trajets as $t): ?>
        <li style="margin-bottom:1rem;">
          <strong><?= htmlspecialchars($t['ville_depart']) ?> → <?= htmlspecialchars($t['ville_arrivee']) ?></strong><br>
          Départ : <?= date('d/m/Y H:i', strtotime($t['date_depart'])) ?><br>
          Statut : <strong><?= htmlspecialchars($t['statut']) ?></strong><br>

          <?php if ($t['statut']==='à_venir'): ?>
            <form action="/trajets/statut" method="POST" style="display:inline;">
              <input type="hidden" name="trajet_id" value="<?= (int)$t['id'] ?>">
              <input type="hidden" name="statut" value="en_cours">
              <button class="btn" type="submit">Démarrer</button>
            </form>
          <?php elseif ($t['statut']==='en_cours'): ?>
            <form action="/trajets/statut" method="POST" style="display:inline;">
              <input type="hidden" name="trajet_id" value="<?= (int)$t['id'] ?>">
              <input type="hidden" name="statut" value="terminé">
              <button class="btn" type="submit">Terminer</button>
            </form>
          <?php else: ?>
            <span style="color:green;">✔ Terminé</span>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>Vous n'avez pas encore publié de trajet.</p>
    <a class="btn" href="/trajets/creer">Créer un nouveau trajet</a>
  <?php endif; ?>
  
<a class="btn" href="/mon_espace">en arrière</a>
</main>
<?php include ROOT.'/includes/footer.php'; ?>
</body></html>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><title>Écrire un avis</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<?php include dirname(__DIR__,3).'/includes/header.php'; ?>
<main class="container">
  <h2>Écrire un avis</h2>
  <p>
    Trajet: <?= htmlspecialchars($reservation['ville_depart']) ?> → <?= htmlspecialchars($reservation['ville_arrivee']) ?>
    (<?= date('d/m/Y H:i', strtotime($reservation['date_depart'])) ?>)
  </p>

 <form action="/avis" method="post">
  <input type="hidden" name="reservation_id" value="<?= (int)$reservation['id'] ?>">
  <label>Note</label>
  <select name="note" required>
    <?php for ($i=5;$i>=1;$i--): ?>
      <option value="<?= $i ?>"><?= $i ?></option>
    <?php endfor; ?>
  </select>

  <label>Commentaire</label>
  <textarea name="commentaire" rows="5" required></textarea>

  <button class="btn" type="submit">Envoyer</button>
  <a class="btn" href="/mes_reservations">Annuler</a>
</form>

</main>
<?php include dirname(__DIR__,3).'/includes/footer.php'; ?>
</body>
</html>

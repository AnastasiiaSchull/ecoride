<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mon Espace - EcoRide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
  <?php include_once dirname(__DIR__,3).'/includes/header.php'; ?>

  <main class="container" style="margin-top: 2rem;">
    <?php
$photoFile = $user['photo'] ?? '';
$photoPath = ROOT . '/public/assets/uploads/' . $photoFile;
$photoUrl  = '/assets/uploads/' . htmlspecialchars($photoFile);
$ver       = ($photoFile && is_file($photoPath)) ? filemtime($photoPath) : time();
?>
<?php if (!empty($photoFile)): ?>
  <img src="<?= $photoUrl ?>?v=<?= $ver ?>" alt="Photo" class="avatar">
<?php endif; ?>


    <h2>Bienvenue, <?= htmlspecialchars($user['pseudo']) ?> !</h2>

    <section style="margin-top: 2rem;">
      <h3>Vos rôles :</h3>
      <ul>
        <?php foreach ($roles as $role): ?>
          <li><?= ucfirst(htmlspecialchars($role)) ?></li>
        <?php endforeach; ?>
      </ul>
      <a href="/roles/edit" class="btn">Modifier mes rôles</a>
    </section>

    <?php if (in_array('conducteur', $roles, true)): ?>
      <section style="margin-top: 3rem;">
        <h3>Photo de profil</h3>
        <?php if (!empty($user['photo'])): ?>
          <img src="/assets/uploads/<?= htmlspecialchars($user['photo']) ?>" alt="Photo" class="avatar">
        <?php endif; ?>
        <hr>
        <form action="/profil/upload-photo" method="post" enctype="multipart/form-data">
          <label>Choisir une photo :
            <input type="file" name="photo" accept="image/*" required>
          </label><br>
          <button type="submit" class="btn">Mettre à jour</button>
        </form>
      </section>

      <section style="margin-top: 3rem;">
        <hr>
        <h3>Vos véhicules enregistrés</h3>
        <?php if ($vehicules): ?>
          <ul>
            <?php foreach ($vehicules as $v): ?>
              <li>
                <?= htmlspecialchars($v['marque']) ?> <?= htmlspecialchars($v['modele']) ?> —
                <?= htmlspecialchars($v['couleur']) ?> (<?= htmlspecialchars($v['energie']) ?>) —
                <?= (int)$v['places'] ?> places
              </li>
            <?php endforeach; ?>
          </ul>
          <div style="margin-top:.75rem;">
          <a href="/vehicules/creer" class="btn">Ajouter un véhicule</a>
        </div>
        <?php else: ?>
          <p>Vous n'avez encore ajouté aucun véhicule.</p>
          <div style="margin-top:.75rem;">
          <a href="/vehicules/creer" class="btn">Ajouter un véhicule</a>
        </div>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <hr>
    <section style="margin-top: 2rem;">
      <h3>Votre solde de crédits :</h3>
      <p><strong><?= (int)$credits ?> crédits</strong> disponibles</p>
      <a href="/credits" class="btn" style="margin-top:.5rem; display:inline-block;">
        Ajouter des crédits
      </a>
    </section>

    <?php if ($credits_utilises !== null): ?>
      <section style="margin-top: 2rem;">
        <h3>Crédits utilisés (en tant que passager) :</h3>
        <p><?= (int)$credits_utilises ?> trajet(s) réservé(s).</p>
      </section>
    <?php endif; ?>

    <?php if ($credits_gagnes !== null): ?>
      <hr>
      <section style="margin-top: 2rem;">
        <h3>Crédits gagnés (en tant que conducteur) :</h3>
        <p><?= (int)$credits_gagnes ?> réservation(s) reçue(s).</p>
      </section>
    <?php endif; ?>

    <hr>
    <?php if (in_array('conducteur', $roles, true)): ?>
      <a href="/mes_trajets" class="btn">Gérer mes trajets</a>
      <a href="/trajets/creer" class="btn">Créer un trajet</a>
    <?php endif; ?>
    <a href="/mes_reservations" class="btn">Voir mes réservations</a>

    <?php if (in_array('passager', $roles, true)): ?>
  <hr>
  <section style="margin-top:2rem;">
    <h3>Laisser un avis</h3>

    <?php if (!empty($conducteursAvis)): ?>
      <form action="/avis" method="post">
        <label>Conducteur :
          <select name="conducteur_id" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($conducteursAvis as $c): ?>
              <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['pseudo']) ?></option>
            <?php endforeach; ?>
          </select>
        </label><br><br>

        <label>Note :
          <select name="note" required>
            <option value="5">5 - Excellent</option>
            <option value="4">4 - Bien</option>
            <option value="3">3 - Correct</option>
            <option value="2">2 - Moyen</option>
            <option value="1">1 - Mauvais</option>
          </select>
        </label><br><br>

        <label>Commentaire :<br>
          <textarea name="commentaire" rows="4" required></textarea>
        </label><br><br>

        <button class="btn" type="submit">Envoyer l'avis</button>
      </form>
    <?php else: ?>
      <p>Aucun conducteur à évaluer pour le moment.</p>
    <?php endif; ?>
  </section>
<?php endif; ?>

  </main>

  <?php include_once dirname(__DIR__,3).'/includes/footer.php'; ?>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Recherche de covoiturage - EcoRide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
<?php include_once dirname(__DIR__,3) . '/includes/header.php'; ?>

<main>
  <section class="hero">
    <img src="/assets/images/hero.jpg" alt="EcoRide Image de fond">
    <h1 class="desktop-only text-outline">Eco-covoiturage</h1>
  </section>

  <!-- formulaire de recherche (GET /covoiturages) -->
  <section class="search">
    <h2 class="h2-bienvenue">Recherche d’un trajet</h2>
    <form action="/covoiturages" method="get" class="search-form">
      <div class="option-group">
        <label class="radio-inline"><input type="radio" name="type" value="depart" <?= ($type ?? '')==='depart'?'checked':''; ?>> Départ</label>
        <select name="depart" id="depart">
          <option value="">Choisir une ville</option>
          <?php foreach ($villesDepart as $ville): ?>
            <option value="<?= htmlspecialchars($ville) ?>" <?= (isset($depart)&&$depart===$ville)?'selected':''; ?>>
              <?= htmlspecialchars($ville) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="option-group">
        <label class="radio-inline"><input type="radio" name="type" value="destination" <?= ($type ?? '')==='destination'?'checked':''; ?>> Destination</label>
        <select name="destination" id="destination">
          <option value="">Choisir une ville</option>
          <?php foreach ($villesArrivee as $ville): ?>
            <option value="<?= htmlspecialchars($ville) ?>" <?= (isset($destination)&&$destination===$ville)?'selected':''; ?>>
              <?= htmlspecialchars($ville) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="date-picker">
        <i class="bi bi-calendar4-week"></i>
        <input type="date" name="date" value="<?= htmlspecialchars($date ?? date('Y-m-d')) ?>">
      </div>

      <div class="passager-block">
        <i class="bi bi-person"></i>
        <input type="number" name="passager" min="1" max="8" value="<?= (int)($passager ?? 1) ?>" class="short-input">
        <span>passager</span>
      </div>

      <button type="submit">Rechercher</button>
    </form>
  </section>

  <h3 class="h3_covoiturage">Résultats — <?= htmlspecialchars($trajetTitre ?? 'Trajets') ?></h3>

  <?php if (empty($trajets)): ?>
    <p>Aucun trajet trouvé pour cette recherche.</p>
  <?php else: ?>
    <section class="trajets-list">
      <?php foreach ($trajets as $trajet): ?>
        <div class="trajet-card">
          <div class="trajet-card-top">
            <div class="trajet-time-block">
              <div class="times">
                <span class="heure"><?= date('H:i', strtotime($trajet['date_depart'])) ?></span>
                <div class="timeline">
                  <span class="circle"></span><span class="line"></span>
                  <span class="duree">
                    <?= gmdate("H\hi", strtotime($trajet['date_arrivee']) - strtotime($trajet['date_depart'])) ?>
                  </span>
                  <span class="line"></span><span class="circle"></span>
                </div>
                <span class="heure"><?= date('H:i', strtotime($trajet['date_arrivee'])) ?></span>
              </div>
              <div class="villes">
                <span><?= htmlspecialchars($trajet['ville_depart']) ?></span>
                <span><?= htmlspecialchars($trajet['ville_arrivee']) ?></span>
              </div>
            </div>
            <div class="prix-block">
              <?php $prixParts = explode('.', number_format((float)$trajet['prix'], 2)); ?>
              <span class="prix-euro"><?= $prixParts[0] ?><sup><?= $prixParts[1] ?></sup> €</span>
            </div>
          </div>

          <div class="trajet-card-bottom">
            <p>Conducteur : <?= htmlspecialchars($trajet['conducteur_nom'] ?? '—') ?>
              <?php $note = $trajet['conducteur_note'] ?? '–'; ?>
              <span>(⭐ <?= htmlspecialchars((string)$note) ?>)</span>
            </p>
            <p>Places restantes : <?= (int)$trajet['places_dispo'] ?></p>

            <!-- Boutons -->
            <div style="display:flex; gap:.5rem; align-items:center;">
              <a href="/trajets/details?id=<?= (int)$trajet['id'] ?>" class="btn details-btn">DETAILS</a>

              <?php if ((int)$trajet['places_dispo'] > 0): ?>
                <?php if (!empty($_SESSION['user_id'])): ?>
                  <form method="post" action="/reservations" style="display:inline;">
                    <input type="hidden" name="trajet_id" value="<?= (int)$trajet['id'] ?>">
                    <button type="submit" class="btn">Réserver</button>
                  </form>
                <?php else: ?>
                  <a class="btn" href="/connexion">Se connecter pour réserver</a>
                <?php endif; ?>
              <?php else: ?>
                <button class="btn btn-disabled" disabled>Complet</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</main>

<?php include_once dirname(__DIR__,3) . '/includes/footer.php'; ?>
</body>
</html>

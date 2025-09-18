<?php session_start();

require_once __DIR__ . '/../../config/db.php';



// obtenir la liste des villes de départ
$stmt = $pdo->query("SELECT  DISTINCT  ville_depart FROM trajets ORDER BY ville_depart ASC");
$villesDepart = $stmt->fetchAll(PDO::FETCH_COLUMN);

// obtenir la liste des villes de destination
$stmt = $pdo->query("SELECT  DISTINCT ville_arrivee FROM trajets ORDER BY ville_arrivee ASC");
$villesArrivee = $stmt->fetchAll(PDO::FETCH_COLUMN);

// obtenir le premier trajet de la base par défaut
$requete = $pdo->query("SELECT DISTINCT id ,ville_depart, ville_arrivee FROM trajets ORDER BY id ASC LIMIT 1");
$premierTrajet = $requete->fetch();

// récupération des paramètres depuis GET
$depart = $_GET['depart'] ?? $premierTrajet['ville_depart'];
$destination = $_GET['destination'] ?? $premierTrajet['ville_arrivee'];
$date = $_GET['date'] ?? date('Y-m-d');
$passager = $_GET['passager'] ?? 1;
$type = $_GET['type'] ?? '';
$filtre = $_GET['filtre'] ?? 'ecologique';

// construction du titre
$trajetTitre = "{$depart} → {$destination}";

// si le filtre prixmin est sélectionné — obtenir le prix minimum
if ($filtre === 'prixmin') {
  $sqlMinPrix = "SELECT MIN(prix) FROM trajets 
                WHERE ville_depart = :depart 
                AND ville_arrivee = :destination 
                AND DATE(date_depart) >= CURDATE()
                AND places_dispo >= :passager";
  $stmtMinPrix = $pdo->prepare($sqlMinPrix);
  $stmtMinPrix->execute([
    'depart' => $depart,
    'destination' => $destination,
    'passager' => $passager
  ]);
  $prixMin = $stmtMinPrix->fetchColumn();
}

if ($filtre === 'prixmax') {
  $sqlMaxPrix = "SELECT MAX(prix) FROM trajets 
                WHERE ville_depart = :depart 
                AND ville_arrivee = :destination 
                AND DATE(date_depart) >= CURDATE()
                AND places_dispo >= :passager";
  $stmtMaxPrix = $pdo->prepare($sqlMaxPrix);
  $stmtMaxPrix->execute([
    'depart' => $depart,
    'destination' => $destination,
    'passager' => $passager
  ]);
  $prixMax = $stmtMaxPrix->fetchColumn();
}

// définition du tri
$orderBy = match ($filtre) {
  'prix' => 't.prix ASC',
  'duree' => 'TIMESTAMPDIFF(MINUTE, t.date_depart, t.date_arrivee) ASC',
  'note' => 'conducteur_note DESC',
  default => 't.eco DESC'
};

// construction de la requête SQL principale
$sql = "SELECT 
            t.*, 
            u.pseudo AS conducteur_nom,
             ROUND(n.note_moyenne, 1) AS conducteur_note
            FROM trajets t
            JOIN users u ON t.conducteur_id = u.id
            LEFT JOIN (
                SELECT conducteur_id, AVG(note) AS note_moyenne
                FROM avis
                WHERE approuve = 1
                GROUP BY conducteur_id
            ) n ON t.conducteur_id = n.conducteur_id
       
        WHERE t.ville_depart = :depart
          AND t.ville_arrivee = :destination          
          AND DATE(t.date_depart) >= CURDATE()
          AND t.places_dispo >= :passager";

// ajout de la condition sur le prix minimum, si nécessaire
if ($filtre === 'prixmin') {
  $sql .= " AND t.prix = :prixMin";
}
if ($filtre === 'prixmax') {
  $sql .= " AND t.prix = :prixMax";
}
if ($filtre === 'electrique') {
  $sql .= " AND t.eco = 1";
}

// finalisation de la requête
$sql .= " ORDER BY $orderBy";


// préparation des paramètres
$params = [
  'depart' => $depart,
  'destination' => $destination,
  'passager' => $passager
];
if ($filtre === 'prixmin') {
  $params['prixMin'] = $prixMin;
}
if ($filtre === 'prixmax') {
  $params['prixMax'] = $prixMax;
}


// exécution de la requête
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$trajets = $stmt->fetchAll();

$conducteursIds = array_column($trajets, 'conducteur_id');

if (!empty($conducteursIds)) {
  $placeholders = implode(',', array_fill(0, count($conducteursIds), '?'));
  $sqlNotes = "SELECT conducteur_id, note FROM avis WHERE approuve = 1 AND conducteur_id IN ($placeholders)";
  $stmtNotes = $pdo->prepare($sqlNotes);
  $stmtNotes->execute($conducteursIds);
  $notesData = $stmtNotes->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

  //ajout du tableau des notes à chaque trajet
  foreach ($trajets as &$trajet) {
    $cid = $trajet['conducteur_id'];
    $trajet['notes'] = $notesData[$cid] ?? [];
  }
  unset($trajet); // ceci est important pour la sécurité (sinon $trajet resterait une référence)
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recherche de covoiturage - EcoRide</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <script src="https://unpkg.com/lucide@latest"></script>

</head>

<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>


  <main>
    <section class="hero">
      <img src="/assets/images/hero.jpg" alt="EcoRide Image de fond">

      <h1>
        <span class="desktop-only text-outline">Economie + ecologi + convivalite = covoiturage</span>

        <div class="mobile-only banner-text">
          <div class="text-top-right">Covoiturage</div>
          <div class="text-bottom-left">+trajet</div>
        </div>

      </h1>
    </section>

    <section class="search">
      <h2 class="desktop-only h2-bienvenue">Recherche d’un trajet</h2>
      <form action="covoiturages.php" method="get" class="search-form desktop-only-form-covoiturages">

        <div class="option-group">
          <label class="radio-inline">
            <input type="radio" name="type" value="depart" <?= $type === 'depart' ? 'checked' : '' ?>> Départ
          </label>
          <select name="depart" id="depart">
            <option value="">Choisir une ville</option>
            <?php foreach ($villesDepart as $ville): ?>
              <option value="<?= htmlspecialchars($ville) ?>" <?= $ville === $depart ? 'selected' : '' ?>>
                <?= htmlspecialchars($ville) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="option-group">
          <label class="radio-inline">
            <input type="radio" name="type" value="destination" <?= $type === 'destination' ? 'checked' : '' ?>>
            Destination
          </label>
          <select name="destination" id="destination">
            <option value="">Choisir une ville</option>
            <?php foreach ($villesArrivee as $ville): ?>
              <option value="<?= htmlspecialchars($ville) ?>" <?= $ville === $destination ? 'selected' : '' ?>>
                <?= htmlspecialchars($ville) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="date-picker" id="custom-date-trigger">
          <i class="bi bi-calendar4-week"></i>
          <input type="hidden" name="date" id="real-date" value="<?= htmlspecialchars($date) ?>">
          <div class="calendar-popup" id="calendar-popup" style="display:none;"></div>
          <span id="date-label"><?= $date ? date('d/m/Y', strtotime($date)) : "Aujourd'hui" ?></span>
        </div>

        <div>
          <i class="bi bi-person"></i>
          <input type="number" name="passager" value="<?= htmlspecialchars($passager) ?>" min="1" max="8"
            class="short-input" placeholder="Passagers">
          <span>passager</span>
          <div id="places-info" class="info-text"></div>
        </div>

        <button type="submit">Rechercher</button>
        <span id="form-error" class="error-message hidden"></span>
      </form>
    </section>

    <!-- version MOBILE -->
    <form class="mobile-inline-form  mobile-only" action="../pages/covoiturages.php" method="get">
      <i data-lucide="circle" class="icon-city"></i>
      <select name="depart">
        <option value="">Départ</option>
        <?php foreach ($villesDepart as $ville): ?>
          <option value="<?= htmlspecialchars($ville) ?>" <?= $ville === $depart ? 'selected' : '' ?>>
            <?= htmlspecialchars($ville) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <span class="arrow-icon" data-lucide="arrow-left-right"></span>
      <i data-lucide="circle" class="icon-city"></i>
      <select name="destination">
        <option value="">Destination</option>
        <?php foreach ($villesArrivee as $ville): ?>
          <option value="<?= htmlspecialchars($ville) ?>" <?= $ville === $destination ? 'selected' : '' ?>>
            <?= htmlspecialchars($ville) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <input type="hidden" name="type" value="mobile" />

      <button type="submit">Rechercher</button>
    </form>
    <hr class="desktope-only">

    <section>    
      <h3 class="h3_covoiturage  ">Trier par</h3> 
      <form method="get" class="filtre-form">

        <div class="filters">
                                 
          <label><input type="radio" name="filtre" value="ecologique" <?= $filtre === 'ecologique' ? 'checked' : '' ?>>
            Écologique</label>
          <label><input type="radio" name="filtre" value="prixmax" <?= $filtre === 'prixmax' ? 'checked' : '' ?>> Prix max
            €</label>
          <label class="desktop-only"><input type="radio" name="filtre" value="prixmin" <?= $filtre === 'prixmin' ? 'checked' : '' ?>> Prix min
            €</label>
          <label><input type="radio" name="filtre" value="duree" <?= $filtre === 'duree' ? 'checked' : '' ?>> Durée min
            h</label>
          <label><input type="radio" name="filtre" value="note" <?= $filtre === 'note' ? 'checked' : '' ?>> Note max
            ⭐</label>
        </div>

        <!-- champs cachés pour conserver les autres paramètres -->
        <input type="hidden" name="type" value="<?= $type ?>">
        <input type="hidden" name="depart" value="<?= htmlspecialchars($depart) ?>">
        <input type="hidden" name="destination" value="<?= htmlspecialchars($destination) ?>">
        <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
        <input type="hidden" name="passager" value="<?= htmlspecialchars($passager) ?>">
      </form>
    </section>

    <h3 class="h3_covoiturage"> Trajets trouves</h3>

    <p class="results-header">
      <?= $date ? date('d/m/Y', strtotime($date)) : "Aujourd'hui" ?>
      &nbsp; &nbsp;&nbsp;
      <?= htmlspecialchars($trajetTitre) ?>
    </p>

    <?php if (empty($trajets)): ?>
      <p>Aucun trajet trouvé pour cette recherche.</p>
    <?php else: ?>
      <?php foreach ($trajets as $trajet): ?>
        <?php $note = $trajet['conducteur_note'] ?? '–'; ?>

        <!-- carte -->
      <?php endforeach; ?>
    <?php endif; ?>

    <section class="trajets-list">
      <?php foreach ($trajets as $trajet): ?>

        <div class="trajet-card">
          <div class="trajet-card-top">
            <div class="trajet-time-block">
              <div class="times">
                <span class="heure"><?= date('H:i', strtotime($trajet['date_depart'])) ?></span>
                <div class="timeline">
                  <span class="circle"></span>
                  <span class="line"></span>
                  <span
                    class="duree"><?= gmdate("H\hi", strtotime($trajet['date_arrivee']) - strtotime($trajet['date_depart'])) ?></span>
                  <span class="line"></span>
                  <span class="circle"></span>
                </div>
                <span class="heure"><?= date('H:i', strtotime($trajet['date_arrivee'])) ?></span>
              </div>
              <div class="villes">
                <span><?= htmlspecialchars($trajet['ville_depart']) ?></span>
                <span><?= htmlspecialchars($trajet['ville_arrivee']) ?></span>
              </div>
            </div>
            <div class="prix-block">
              <?php
              $prixParts = explode('.', number_format($trajet['prix'], 2));
              ?>
              <span class="prix-euro"><?= $prixParts[0] ?><sup><?= $prixParts[1] ?></sup> €</span>
            </div>
          </div>

          <div class="trajet-card-bottom desktop-only">
            <?php $note = $trajet['conducteur_note'] ?? '–'; ?>
            <?php $notes = $trajet['notes'] ?? []; ?>

            <p>Conducteur : <?= htmlspecialchars($trajet['conducteur_nom']) ?> <span>(⭐ <?= $note ?>)</span></p>
            <p>Notes : <?= !empty($notes) ? implode(', ', $notes) : '–' ?></p>
            <p>Places restantes : <?= $trajet['places_dispo'] ?></p>
            <p>Prix : <?= number_format($trajet['prix'], 2) ?>€ Heure :
              <?= date('H:i', strtotime($trajet['date_depart'])) ?> ➔
              <?= date('H:i', strtotime($trajet['date_arrivee'])) ?>
            </p>
            <?php if ($trajet['eco']): ?>
              <p>Écologique</p>
            <?php endif; ?>
            <a href="details.php?id=<?= $trajet['id'] ?>" class="btn details-btn">DETAILS</a>
          </div>
           <a href="details.php?id=<?= $trajet['id'] ?>" class="btn details-btn mobile-only">DETAILS</a>
        </div>


      <?php endforeach; ?>
    </section>

    <script src="/assets/js/covoiturage-datepicker.js"></script>
    <script>
      document.querySelectorAll('.filters input[type=radio]').forEach(radio => {
        radio.addEventListener('change', () => {
          radio.closest('form').submit();
        });
      });
    </script>

    <script src="/assets/js/form-error-checker.js"></script>
    
    <?php include_once __DIR__ . '/../../includes/footer.php'; ?>

    <script>
      lucide.createIcons();
    </script>

</body>

</html>
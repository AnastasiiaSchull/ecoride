<?php session_start(); ?>
<?php
require_once '../config/db.php';
$sql = "SELECT DISTINCT ville_depart FROM trajets";
$villesDepart = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);

$sql = "SELECT DISTINCT ville_arrivee FROM trajets";
$villesArrivee = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);

// получить 3 ближайших маршрута
$sql = "SELECT ville_depart, ville_arrivee, MIN(date_depart) as prochaine_date
        FROM trajets
        WHERE date_depart >= CURDATE()
        GROUP BY ville_depart, ville_arrivee
        ORDER BY prochaine_date ASC
        LIMIT 3";
$trajetsAVenir = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion - EcoRide</title>
  <link rel="stylesheet" href="../public/assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>

<body>
  <?php include_once '../includes/header.php'; ?>

  <main>
    <section class="hero">
      <img src="assets/images/hero.jpg" alt="EcoRide Image de fond">
      <h1>Bienvenue chez EcoRide</h1>
    </section>

    <section class="search">
      <h2>Recherche d’un trajet</h2>
      <form action="../pages/covoiturages.php" method="get" class="search-form">

        <div class="option-group">
          <label class="radio-inline">
            <input type="radio" name="type" value="depart" checked> Départ
          </label>
          <select name="depart" id="depart">
            <option value="">Choisir une ville</option>
            <?php foreach ($villesDepart as $ville): ?>
              <option value="<?= htmlspecialchars($ville) ?>"><?= htmlspecialchars($ville) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="option-group">
          <label class="radio-inline">
            <input type="radio" name="type" value="destination"> Destination
          </label>
          <select name="destination" id="destination">
            <option value="">Choisir une ville</option>
            <?php foreach ($villesArrivee as $ville): ?>
              <option value="<?= htmlspecialchars($ville) ?>"><?= htmlspecialchars($ville) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="date-picker" id="custom-date-trigger">
          <i class="bi bi-calendar4-week"></i>
          <span id="date-label">Aujourd'hui</span>
          <input type="hidden" name="date" id="real-date">
          <div class="calendar-popup" id="calendar-popup" style="display:none;"></div>
        </div>


        <div>
          <i class="bi bi-person"></i>
          <input type="number" name="passager" value="1" min="1" max="8" class="short-input" placeholder="Passagers">
          <span>passager</span>
          <div id="places-info" class="info-text"></div> <!-- вот сюда -->
        </div>

        <button type="submit">Rechercher</button>
      </form>
    </section>

    <section class="upcoming">
      <h2>Trajets à venir</h2>
     <div class="trajets">
  <?php foreach ($trajetsAVenir as $trajet): ?>
    <a 
      class="trajet" 
      href="../pages/covoiturages.php?depart=<?= urlencode($trajet['ville_depart']) ?>&destination=<?= urlencode($trajet['ville_arrivee']) ?>&date=<?= date('Y-m-d', strtotime($trajet['prochaine_date'])) ?>&passager=1">
      <?= htmlspecialchars($trajet['ville_depart']) ?> → <?= htmlspecialchars($trajet['ville_arrivee']) ?>
    </a>
  <?php endforeach; ?>
</div>

    </section>
  </main>

  <script src="../public/assets/js/covoiturage-datepicker.js"></script>
  
  <?php include_once '../includes/footer.php'; ?>

</body>

</html>
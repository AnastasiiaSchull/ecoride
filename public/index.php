<?php session_start(); ?>
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
    <form action="pages/recherche.php" method="get" class="search-form">   
      <div class="option-group">
        <label><input type="radio" name="type" value="depart" checked></label>
        <input type="text" name="depart" placeholder="Départ">
      </div>

      <div class="option-group">
        <label><input type="radio" name="type" value="destination"></label>
        <input type="text" name="destination" placeholder="Destination">
      </div>



      <div class="date-picker " onclick="document.getElementById('real-date').showPicker()">
        <i class="bi bi-calendar4-week"></i>
        <span id="date-label">Aujourd'hui</span>
        <input type="date" id="real-date" name="date" value="<?= date('Y-m-d') ?>">
      </div>

      <div>
        <i class="bi bi-person"></i>
        <input type="number" name="passager" value="1" min="1" max="8" class="short-input"
          placeholder="Passagers"><span>passager</span>
      </div>
      <button type="submit">Rechercher</button>
    </form>
  </section>

  <section class="upcoming">
    <h2>Trajets à venir</h2>
    <div class="trajets">
      <div class="trajet">Lille → Paris</div>
      <div class="trajet">Paris → Lyon</div>
      <div class="trajet">Reims → Paris</div>
    </div>
  </section>
</main>


<script>
  const dateInput = document.getElementById('real-date');
  const dateLabel = document.getElementById('date-label');

  dateInput.addEventListener('change', function () {
    const date = new Date(this.value);
    const formatted = date.toLocaleDateString('fr-FR'); // formate comme 01/07/2025
    dateLabel.textContent = formatted;
  });
</script>

<?php include_once '../includes/footer.php'; ?>

</body>
</html>
<?php
session_start();
require_once '../config/db.php';

$type = $_GET['type'] ?? null;
$date = $_GET['date'] ?? date('Y-m-d');

if ($type === 'depart' && !empty($_GET['depart'])) {
    $ville = $_GET['depart'];
    $query = "SELECT * FROM trajets WHERE ville_depart = ? AND DATE(date_depart) = ?";
} elseif ($type === 'destination' && !empty($_GET['destination'])) {
    $ville = $_GET['destination'];
    $query = "SELECT * FROM trajets WHERE ville_arrivee = ? AND DATE(date_depart) = ?";
} else {
    exit("Erreur : veuillez choisir un critère et une ville.");
}

$stmt = $pdo->prepare($query);
$stmt->execute([$ville, $date]);
$trajets = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Résultats de recherche</title>
  <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>

<?php include_once '../includes/header.php'; ?>

<main>
  <section class="search-results">
    <h2>Résultats pour le <?= htmlspecialchars($ville) ?> le <?= htmlspecialchars(date('d/m/Y', strtotime($date))) ?></h2>

    <?php if (empty($trajets)): ?>
      <p>Aucun trajet trouvé pour les critères choisis.</p>
    <?php else: ?>
      <div class="trajets">
        <?php foreach ($trajets as $trajet): ?>
          <div class="trajet">
            <h3><?= htmlspecialchars($trajet['ville_depart']) ?> → <?= htmlspecialchars($trajet['ville_arrivee']) ?></h3>
            <p><strong>Départ :</strong> <?= date('d/m/Y H:i', strtotime($trajet['date_depart'])) ?></p>
            <p><strong>Arrivée :</strong> <?= date('d/m/Y H:i', strtotime($trajet['date_arrivee'])) ?></p>
            <p><strong>Prix :</strong> <?= $trajet['prix'] ?> €</p>
            <p><strong>Places dispo :</strong> <?= $trajet['places_dispo'] ?></p>
            <p><strong>Écologique :</strong> <?= $trajet['eco'] ? 'Oui' : 'Non' ?></p>
    
            <form action="detail.php" method="get">
              <input type="hidden" name="id" value="<?= $trajet['id'] ?>">
              <button type="submit">Détail</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include_once '../includes/footer.php'; ?>
</body>
</html>


<?php session_start();
require_once __DIR__ . '/../../config/db.php';


$id = $_GET['id'] ?? null;

if (!$id) {
  echo "Trajet non trouvé.";
  exit;
}

// on récupère les informations du trajet
$stmt = $pdo->prepare("
    SELECT t.*, 
           u.pseudo, 
           u.photo, 
           v.marque, 
           v.modele, 
           v.energie, 
           ROUND(AVG(a.note), 1) AS note_moyenne,
           GROUP_CONCAT(a.commentaire SEPARATOR '||') AS commentaires
    FROM trajets t
    JOIN users u ON t.conducteur_id = u.id
    LEFT JOIN vehicules v ON t.vehicule_id = v.id
    LEFT JOIN avis a ON a.conducteur_id = u.id AND a.approuve = 1
    WHERE t.id = ?
    GROUP BY t.id
");
$stmt->execute([$id]);
$trajet = $stmt->fetch();

if (!$trajet) {
  echo "Trajet non trouvé.";
  exit;
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion - EcoRide</title>
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/details.css">

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>

  <main>
    <section class="hero">
      <img src="/assets/images/hero.jpg" alt="EcoRide Image de fond">
      <h1>Details</h1>
    </section>
    <section class="detail-container">
      <h2>Détails du trajet</h2>

      <p><strong>De :</strong> <?= htmlspecialchars($trajet['ville_depart']) ?> → <strong>à :</strong>
        <?= htmlspecialchars($trajet['ville_arrivee']) ?></p>
      <p><strong>Départ :</strong> <?= date('d/m/Y H:i', strtotime($trajet['date_depart'])) ?></p>
      <p><strong>Arrivée :</strong> <?= date('d/m/Y H:i', strtotime($trajet['date_arrivee'])) ?></p>
      <p><strong>Prix :</strong> <?= number_format($trajet['prix'], 2) ?> €</p>
      <p><strong>Places disponibles :</strong> <?= $trajet['places_dispo'] ?></p>

      <hr>
      <div class="detail-section">
        <h3>Conducteur</h3>
        <p><strong>Pseudo :</strong> <?= htmlspecialchars($trajet['pseudo']) ?></p>
        <p><strong>Note moyenne :</strong> <?= $trajet['note_moyenne'] ?? '–' ?> ⭐</p>
        <?php if (!empty($trajet['photo'])): ?>
          <img src="/assets/uploads/<?= $trajet['photo'] ?>" alt="Photo du conducteur" class="avatar">
        <?php endif; ?>

        <hr>
        <h3>Véhicule</h3>
        <p><strong>Marque :</strong> <?= htmlspecialchars($trajet['marque']) ?></p>
        <p><strong>Modèle :</strong> <?= htmlspecialchars($trajet['modele']) ?></p>
        <p><strong>Énergie :</strong> <?= htmlspecialchars($trajet['energie']) ?></p>
        <?php if ($trajet['eco']): ?>
          <p style="color:green;"><strong>✔ Écologique (voiture électrique)</strong></p>
        <?php endif; ?>
        <hr>
        <h3>Avis des passagers</h3>
        <?php
        $commentaires = explode('||', $trajet['commentaires']);
        foreach ($commentaires as $commentaire) {
          if (trim($commentaire)) {
            echo "<p>💬 " . htmlspecialchars($commentaire) . "</p>";
          }
        }
        ?>
      </div>
      <br>
      <?php if (isset($_SESSION['user_id'])): ?>
        <?php if ($trajet['places_dispo'] > 0): ?>
          <form id="reserveForm" action="reserver.php" method="POST">
            <input type="hidden" name="trajet_id" value="<?= $trajet['id'] ?>">
            <button type="button" onclick="confirmReservation()" class="btn details-btn">Participer</button>
          </form>
        <?php else: ?>
          <p style="color:red;">Aucune place disponible.</p>
        <?php endif; ?>
      <?php else: ?>
        <p><a href="connexion.php" class="details-btn">Connectez-vous</a> pour participer à ce covoiturage.</p>
      <?php endif; ?>
    </section>

  </main>
 <?php include_once __DIR__ . '/../../includes/footer.php'; ?>

  <script>
    function confirmReservation() {
      Swal.fire({
        title: 'Confirmer la réservation ?',
        text: 'Ce trajet vous coûtera 1 crédit.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Oui, je confirme',
        cancelButtonText: 'Annuler'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('reserveForm').submit();
        }
      });
    }
  </script>
</body>

</html>
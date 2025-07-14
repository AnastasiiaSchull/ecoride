<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Confirmation de réservation</title>
  <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
  <?php include_once '../includes/header.php'; ?>

  <main class="container">
    <h2>Réservation confirmée ! ✅</h2>
    <p>Merci pour votre réservation. Vous recevrez les détails par email.</p>
    <a class="details-btn" href="mon_espace.php">Retour à mon espace</a>
  </main>

  <?php include_once '../includes/footer.php'; ?>
</body>
</html>

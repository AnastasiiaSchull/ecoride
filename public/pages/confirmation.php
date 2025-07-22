<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Confirmation de réservation</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>

  <main class="container">
    <h2 style="color: green;">✔ Réservation confirmée !</h2>
    <p>Merci pour votre réservation. Vous recevrez les détails par email.</p>
    <a class="details-btn" href="mon_espace.php">Retour à mon espace</a>
  </main>
<?php if (isset($_SESSION['flash'])): ?>
  <script>
    Swal.fire({
      title: 'Succès',
      text: "<?= $_SESSION['flash'] ?>",
      icon: 'success',
      confirmButtonText: 'OK'
    });
  </script>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

   <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>

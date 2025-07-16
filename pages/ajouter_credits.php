<?php
session_start();
require_once '../config/db.php';

// vérification de la connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = (int) $_POST['montant'];

    if ($montant > 0) {
        $stmt = $pdo->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
        $stmt->execute([$montant, $user_id]);

        $message = "✔ Votre compte a été crédité de $montant crédits!";
    } else {
        $message = "Montant invalide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter des crédits</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container" style="margin-top: 2rem;">
    <h2>Ajouter des crédits</h2>

    <?php if (!empty($message)): ?>
      <div class="alert-success"><?= htmlspecialchars($message) ?></div>

    <?php endif; ?>

    <form method="POST">
        <label>Montant à ajouter :
            <input type="number" name="montant" min="1" required>
        </label>
        <button type="submit" class="btn">Valider</button>
    </form>

    <p><a href="mon_espace.php">Retour à mon espace</a></p>
</main>

<?php include '../includes/footer.php'; ?>

</body>
</html>

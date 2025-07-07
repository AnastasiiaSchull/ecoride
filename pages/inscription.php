<?php
session_start();
?>

<?php
require_once '../config/db.php'; 

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $pseudo = trim($_POST["pseudo"]);
  $email = trim($_POST["email"]);
  $password = $_POST["password"];
  $confirm_password = $_POST["confirm_password"];
  $role = $_POST["role"];
  $marque = $_POST["marque"] ?? null;
  $couleur = $_POST["couleur"] ?? null;
  $places = $_POST["places"] ?? null;

  if (empty($pseudo) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
    $errors[] = "Tous les champs obligatoires doivent être remplis.";
  }

  if ($password !== $confirm_password) {
    $errors[] = "Les mots de passe ne correspondent pas.";
  }

  // corection du email
  $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetch()) {
    $errors[] = "Cet email est déjà utilisé.";
  }

  if (empty($errors)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO utilisateurs (pseudo, email, mot_de_passe, role, marque, couleur, places)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$pseudo, $email, $hashed_password, $role, $marque, $couleur, $places]);

    header("Location: connexion.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Inscription</title>
  <link rel="stylesheet" href="../public/assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>
</body>
<?php include '../includes/header.php'; ?>
<main>
  <h2 class="search" style="margin-top: 1.125rem">Création de compte</h2>
  <div class="container" style="margin-top: 1.25rem">


    <?php if (!empty($errors)): ?>
      <div class="errors">
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST">
      <label>Pseudo :
        <input type="text" name="pseudo" required>
      </label>

      <label>Email :
        <input type="email" name="email" required>
      </label>

      <label>Mot de passe :
        <input type="password" name="password" required>
      </label>

      <label>Confirmer mot de passe :
        <input type="password" name="confirm_password" required>
      </label>
      <div class="radio">
        <label  class="role"><input type="radio" name="role" value="conducteur" onclick="toggleCarFields()" required>
          Conducteur</label>
        <label><input type="radio" name="role" value="passager" onclick="toggleCarFields()"> Passager</label>
      </div>
      <div id="carFields" style="display: none;">
        <label>Marque de voiture :
          <input type="text" name="marque">
        </label>
        <label>Couleur :
          <input type="text" name="couleur">
        </label>
        <label>Nombre de places :
          <input type="number" name="places" min="1">
        </label>
      </div>
  </div>
  <div style="text-align: center; margin-top: 1rem;">
    <button type="submit" class="btn">Créer un compte</button>
  </div>
  </form>

</main>

<?php include '../includes/footer.php'; ?>
<script>
  function toggleCarFields() {
    const role = document.querySelector('input[name="role"]:checked').value;
    const carFields = document.getElementById('carFields');
    carFields.style.display = role === 'conducteur' ? 'block' : 'none';
  }
</script>
</body>

</html>
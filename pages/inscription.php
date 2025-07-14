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
  if (!isset($_POST["roles"]) || empty($_POST["roles"])) {
    $errors[] = "Veuillez sélectionner au moins un rôle.";
  } else {
    $roles = $_POST["roles"];
  }

  $marque = $_POST["marque"] ?? null;
  $modele = $_POST["modele"] ?? null;
  $couleur = $_POST["couleur"] ?? null;
  $energie = $_POST["energie"] ?? null;
  $places = $_POST["places"] ?? null;

  if (empty($pseudo) || empty($email) || empty($password) || empty($confirm_password) || empty($roles)) {
    $errors[] = "Tous les champs obligatoires doivent être remplis.";
  }

  if ($password !== $confirm_password) {
    $errors[] = "Les mots de passe ne correspondent pas.";
  }

  // corection du email
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetch()) {
    $errors[] = "Cet email est déjà utilisé.";
  }

  if (empty($errors)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // insérer l'utilisateur
    $stmt = $pdo->prepare("INSERT INTO users (pseudo, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$pseudo, $email, $hashed_password]);

    // récupérer l'ID du nouvel utilisateur
    $user_id = $pdo->lastInsertId();

    // trouver id du rôle (depuis table roles)
    foreach ($roles as $role) {
      $stmt = $pdo->prepare("SELECT id FROM roles WHERE nom = ?");
      $stmt->execute([$role]);
      $role_id = $stmt->fetchColumn();

      if ($role_id) {
        // ajouter dans user_roles
        $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $role_id]);
        // si le rôle est passager, ajouter 5 crédits (si encore 0 crédits)
        if ($role === 'passager') {
          $stmt = $pdo->prepare("UPDATE users SET credits = 5 WHERE id = ? AND credits = 0");
          $stmt->execute([$user_id]);
        }
      }
    }
    // si le rôle est conducteur, ajouter un véhicule
    if (in_array('conducteur', $roles)) {
      if (empty($marque) || empty($modele) || empty($couleur) || empty($energie) || empty($places) || $places <= 0) {
        $errors[] = "Tous les champs du véhicule doivent être remplis correctement.";
      } else {
        $stmt = $pdo->prepare("INSERT INTO vehicules (user_id, marque, modele, couleur, energie, places) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $marque, $modele, $couleur, $energie, $places]);
      }
    }

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
      <div class="checkbox">
        <label><input type="checkbox" name="roles[]" value="conducteur" onclick="toggleCarFields()"> Conducteur</label>
        <label><input type="checkbox" name="roles[]" value="passager" onclick="toggleCarFields()"> Passager</label>
      </div>

      <div id="carFields" style="display: none;">
        <label>Marque de voiture :
          <input type="text" name="marque">
        </label>
        <label>Modèle :
          <input type="text" name="modele">
        </label>
        <label>Couleur :
          <input type="text" name="couleur">
        </label>
        <label>Type de carburant :
          <select name="energie" required>
            <option value="">-- Choisissez --</option>
            <option value="essence">Essence</option>
            <option value="diesel">Diesel</option>
            <option value="electrique">Électrique</option>
          </select>
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
    const checkboxes = document.querySelectorAll('input[name="roles[]"]:checked');
    const carFields = document.getElementById('carFields');

    let isConducteurSelected = false;

    checkboxes.forEach((checkbox) => {
      if (checkbox.value === 'conducteur') {
        isConducteurSelected = true;
      }
    });

    carFields.style.display = isConducteurSelected ? 'block' : 'none';
  }

  // еxécuter au chargement de la page (par exemple, si l'utilisateur a cliqué sur "Retour" dans le navigateur)

  window.addEventListener('DOMContentLoaded', toggleCarFields);
</script>

</body>

</html>
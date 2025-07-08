<?php
session_start();
require_once '../config/db.php';

//vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  header("Location: connexion.php");
  exit;
}

//récupère l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Получение ролей пользователя
$stmt = $pdo->prepare("SELECT r.nom FROM roles r
                      JOIN user_roles ur ON r.id = ur.role_id
                      WHERE ur.user_id = ?");
$stmt->execute([$user_id]);
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);


//récupère les véhicules de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM vehicules WHERE user_id = ?");
$stmt->execute([$user_id]);
$vehicules = $stmt->fetchAll();

// получение всех préférences из базы
$stmt = $pdo->query("SELECT * FROM preferences");
$allPreferences = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Mon Espace - EcoRide</title>
  <link rel="stylesheet" href="../public/assets/css/style.css">
</head>

<body>

  <?php include '../includes/header.php'; ?>

  <main class="container" style="margin-top: 2rem;">

    <h2>Bienvenue, <?= htmlspecialchars($user['pseudo']) ?> !</h2>
    <section style="margin-top: 2rem;">
      <h3>Vos rôles :</h3>
      <ul>
        <?php foreach ($roles as $role): ?>
          <li><?= ucfirst(htmlspecialchars($role)) ?></li>
        <?php endforeach; ?>
      </ul>
      <a href="modifier_roles.php" class="btn">Modifier mes rôles</a>

    </section>

    <?php if (in_array('conducteur', $roles)): ?>
      <section style="margin-top: 3rem;">
        <h3>Ajouter un véhicule</h3>
        <form method="POST" action="traitement_vehicule.php">
          <label>Marque : <input type="text" name="marque" required></label><br>
          <label>Modèle : <input type="text" name="modele" required></label><br>
          <label>Couleur : <input type="text" name="couleur" required></label><br>
          <label>Énergie :
            <select name="energie">
              <option value="essence">Essence</option>
              <option value="diesel">Diesel</option>
              <option value="electrique">Électrique</option>
            </select>
          </label><br>
          <label>Places disponibles : <input type="number" name="places" min="1" required></label><br>
          <label>Préférences :</label><br>
          <?php foreach ($allPreferences as $pref): ?>
            <input type="checkbox" name="preferences[]" value="<?= htmlspecialchars($pref['id']) ?>">
            <?= htmlspecialchars(ucfirst($pref['nom'])) ?><br>
          <?php endforeach; ?>

          <button type="submit" class="btn">Ajouter le véhicule</button>
        </form>
      </section>



      <section style="margin-top: 3rem;">
        <h3>Vos véhicules enregistrés</h3>
        <!-- affichage des véhicules déjà ajoutés -->
        <?php if (count($vehicules) > 0): ?>
          <ul>
            <?php foreach ($vehicules as $v): ?>
              <li>
                <?= htmlspecialchars($v['marque']) ?>       <?= htmlspecialchars($v['modele']) ?> -
                <?= htmlspecialchars($v['couleur']) ?> (<?= $v['energie'] ?>) -
                <?= $v['places'] ?> places
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>Vous n'avez encore ajouté aucun véhicule.</p>
        <?php endif; ?>
      </section>
    <?php endif; ?>
  </main>

  <?php include '../includes/footer.php'; ?>
</body>

</html>
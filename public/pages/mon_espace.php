<?php
session_start();
require_once __DIR__ . '/../../config/db.php';



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

// récupération des rôles de l'utilisateur

$stmt = $pdo->prepare("SELECT r.nom FROM roles r
                      JOIN user_roles ur ON r.id = ur.role_id
                      WHERE ur.user_id = ?");
$stmt->execute([$user_id]);
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);


//récupère les véhicules de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM vehicules WHERE user_id = ?");
$stmt->execute([$user_id]);
$vehicules = $stmt->fetchAll();

// récupération de toutes les préférences depuis la base de données

$stmt = $pdo->query("SELECT * FROM preferences");
$allPreferences = $stmt->fetchAll();

// récupère le nombre de crédits restants (в любом случае пригодится)
$credits = (int) $user['credits'];

// crédits utilisés (si passager)
$credits_utilises = null;
if (in_array('passager', $roles)) {
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE passager_id = ?");
  $stmt->execute([$user_id]);
  $credits_utilises = $stmt->fetchColumn(); // 1 réservation = 1 crédit
}

// crédits gagnés (si conducteur)
$credits_gagnes = null;
if (in_array('conducteur', $roles)) {
  $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM reservations r 
        JOIN trajets t ON r.trajet_id = t.id 
        WHERE t.conducteur_id = ?
    ");
  $stmt->execute([$user_id]);
  $credits_gagnes = $stmt->fetchColumn();
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Mon Espace - EcoRide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

  <?php include_once __DIR__ . '/../../includes/header.php'; ?>

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
        <!-- section photo de profil -->
        <section style="margin-top: 2rem;">
          <h3>Photo de profil</h3>

          <?php if (!empty($user['photo'])): ?>
            <img src="/assets/uploads/<?= htmlspecialchars($user['photo']) ?>" alt="Photo du conducteur"
              class="avatar">
          <?php endif; ?>
          <hr>
          <form action="upload_photo.php" method="post" enctype="multipart/form-data">
            <label>Choisir une photo :
              <input type="file" name="photo" accept="image/*" required>
            </label><br>
            <button type="submit" class="btn">Mettre à jour</button>
          </form>

        </section>
        <hr>
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
        <hr>
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
    <hr>
    <section style="margin-top: 2rem;">
      <h3>Votre solde de crédits :</h3>
      <p><strong><?= $credits ?> crédits</strong> disponibles</p>
      <a href="ajouter_credits.php" class="btn" style="margin-top: 0.5rem; display: inline-block;">Ajouter des
        crédits</a>
    </section>

    <?php if ($credits_utilises !== null): ?>
      <section style="margin-top: 2rem;">
        <h3>Crédits utilisés (en tant que passager) :</h3>
        <p><?= $credits_utilises ?> trajet(s) réservé(s), donc <?= $credits_utilises ?> crédit(s) utilisés.</p>
      </section>
    <?php endif; ?>
    <hr>
    <?php if ($credits_gagnes !== null): ?>
      <section style="margin-top: 2rem;">
        <h3>Crédits gagnés (en tant que conducteur) :</h3>
        <p><?= $credits_gagnes ?> réservation(s) reçue(s), donc <?= $credits_gagnes ?> crédit(s) gagnés.</p>
      </section>
    <?php endif; ?>
    <hr>
    <?php if (in_array('conducteur', $roles)): ?>
      <a href="mes_trajets.php" class="btn">Gérer mes trajets</a>
    <?php endif; ?>
    <a href="mes_reservations.php" class="btn">Voir mes réservations</a>

    <hr>
    <section style="margin-top: 2rem;">
      <h3>Laisser un avis</h3>
      <?php
      // récupérer tous les conducteurs avec qui l'utilisateur a réellement voyagé sur un trajet terminé
      
      $stmt = $pdo->prepare("
        SELECT DISTINCT u.id, u.pseudo
        FROM users u
        JOIN trajets t ON u.id = t.conducteur_id
        JOIN reservations r ON r.trajet_id = t.id
        WHERE r.passager_id = ?
          AND r.statut = 'confirmee'
          AND t.date_depart < NOW()
      ");
      $stmt->execute([$user_id]);
      $conducteurs = $stmt->fetchAll();
      ?>

      <form method="POST" action="ajouter_avis.php">
        <label>Conducteur :
          <select name="conducteur_id" required>
            <option value="">-- Sélectionner --</option>
            <?php foreach ($conducteurs as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['pseudo']) ?></option>
            <?php endforeach; ?>
          </select>
        </label><br><br>

        <label>Note :
          <select name="note" required>
            <option value="5">5 - Excellent</option>
            <option value="4">4 - Bien</option>
            <option value="3">3 - Correct</option>
            <option value="2">2 - Moyen</option>
            <option value="1">1 - Mauvais</option>
          </select>
        </label><br><br>

        <label>Commentaire :<br>
          <textarea name="commentaire" rows="4" cols="50" required></textarea>
        </label><br><br>

        <button type="submit" class="btn">Envoyer l'avis</button>
      </form>
      <?php if (isset($_GET['success']) && $_GET['success'] === 'avis'): ?>
        <p style="color: green;">Merci pour votre avis ! Il sera modéré prochainement.</p>
      <?php elseif (isset($_GET['error']) && $_GET['error'] === 'champ'): ?>
        <p style="color: red;">Veuillez remplir tous les champs.</p>
      <?php endif; ?>

    </section>

  </main>

   <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>
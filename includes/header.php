<header class="main-header">
  <nav class="navbar">
    <a class="logo" href="/ecoride/public/index.php">EcoRide</a>
    <a class="nav-links" href="/ecoride/public/index.php">Accueil</a>
    <a class="nav-links" href="/ecoride/pages/covoiturages.php">Covoiturages</a>
    <a class="nav-links" href="/ecoride/pages/contact.php">Contact</a>
    <?php if (!isset($_SESSION['user'])): ?>
      <a class="nav-links" href="/ecoride/pages/connexion.php">Connexion</a>

    <?php else: ?>
       <?php
    $photo = $_SESSION['user']['photo'] ?? 'default.png';
    $avatarPath = "/ecoride/public/assets/uploads/" . htmlspecialchars($photo);
  ?>
      <?php if (in_array('admin', $_SESSION['user']['roles'])): ?>
        <a class="nav-links with-avatar" href="/ecoride/pages/admin.php"> <img src="<?= $avatarPath ?>" alt="avatar" class="avatar">Admin</a>
      <?php else: ?>
        <a class="nav-links with-avatar" href="/ecoride/pages/mon_espace.php"> <img src="<?= $avatarPath ?>" alt="avatar" class="avatar">Mon Espace</a>
      <?php endif; ?>
      <?php if (in_array('employe', $_SESSION['user']['roles'])): ?>
        <a class="nav-links with-avatar" href="/employe/avis.php"> <img src="<?= $avatarPath ?>" alt="avatar" class="avatar">Espace Employé</a>
      <?php endif; ?>

      <a class="nav-links with-avatar" href="/ecoride/pages/logout.php">Déconnexion</a>
    <?php endif; ?>

  </nav>
</header>
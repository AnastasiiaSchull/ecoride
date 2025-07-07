<header class="main-header">
  <nav class="navbar">
    <a class="logo" href="/ecoride/public/index.php">EcoRide</a>
    <a class="nav-links" href="/ecoride/public/index.php">Accueil</a>
    <a class="nav-links" href="/ecoride/pages/covoiturages.php">Covoiturages</a>
    <a class="nav-links" href="/ecoride/pages/contact.php">Contact</a>
    <?php if (!isset($_SESSION['user'])): ?>
      <a class="nav-links" href="/ecoride/pages/connexion.php">Connexion</a>
    <?php else: ?>
      <a class="nav-links" href="/pages/espace.php">Mon Espace</a>
      <a class="nav-links" href="/logout.php">Déconnexion</a>

      <?php if ($_SESSION['user']['role'] === 'admin'): ?>
        <a class="nav-links" href="/admin/dashboard.php">Admin</a>
      <?php endif; ?>

      <?php if ($_SESSION['user']['role'] === 'employe'): ?>
        <a class="nav-links" href="/employe/avis.php">Espace Employé</a>
      <?php endif; ?>
    <?php endif; ?>
  </nav>
</header>
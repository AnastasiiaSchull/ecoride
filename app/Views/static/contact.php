<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Contact - EcoRide</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="/assets/css/style.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>
  <?php include ROOT . '/includes/header.php'; ?>

  <main>
    <section class="container" style="margin-top: 2rem; text-align: center;">
      <h3>À propos du développeur</h3>
      <p>Développé par <strong>Anastasiia Schull</strong>, développeuse web en formation chez Studi.</p>
      <p>Compétences : HTML, CSS, JavaScript, PHP, MySQL, Figma, MongoDB, Git, Jira</p>

      <p style="margin-top:.5rem;">
        <strong>Architecture :</strong> MVC (PHP)
      </p>

      <div class="liens-projet">
        <p>
          🔗 <strong>Projet GitHub :</strong>
          <a href="https://github.com/AnastasiiaSchull/ecoride" target="_blank" rel="noopener">github.com/AnastasiiaSchull/ecoride</a>
        </p>
        <p>
          🎨 <strong>Maquettes Figma :</strong>
          <a href="https://www.figma.com/design/IsiAZjrXlyXuE2cKIvvblP/EcoRide?node-id=0-1" target="_blank" rel="noopener">
            Figma – EcoRide
          </a>
        </p>
         <!--  lien vers des wireframes -->
        <p>
          ▣ <strong>Wireframes :</strong>
          <a href="https://1drv.ms/b/c/8fa343be0069556b/ETqKnRgc3-hLoNia1XI1MQoBt_KrPPemt19U_XhRQ2gRYQ?e=npIaDW" target="_blank" rel="noopener">
            Voir les wireframes Excalidraw
          </a>
        </p>
      </div>
    </section>

    <div class="container">
      <p style="margin-top: 1rem; color: #888; text-align: center;">
        <strong>Infos de test :</strong><br>
        Admin → <code>admin@gmail.com</code> / <code>admin</code><br>
        Employé → <code>employe1@gmail.com</code> / <code>motdepasse</code><br>
        User → <code>schull@gmail.com</code> / <code>motdepasse</code>
      </p>
    </div>
  </main>

  <?php include ROOT . '/includes/footer.php'; ?>
</body>
</html>

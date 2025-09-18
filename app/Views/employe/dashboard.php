<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Espace Employé</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .toggle-section { cursor:pointer; background:#eef; padding:10px; border-radius:5px; }
    .content { display:none; margin-top:10px; }
    li { margin-bottom:1rem; }
    form.inline-btn { display:inline; margin-left:10px; }
  </style>
</head>
<body>
  <?php include_once ROOT.'/includes/header.php'; ?>

  <main class="container" style="margin-top:2rem">
    <h2>Espace Employé</h2>

    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="alert-success" style="margin:.75rem 0;">
        <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
      </div>
    <?php endif; ?>

    <!-- modération des avis -->
    <section>
      <h3 class="toggle-section"><i data-lucide="edit"></i> Modération des avis</h3>
      <div class="content">
        <?php if (!empty($avis)): ?>
          <ul>
            <?php foreach ($avis as $a): ?>
              <li>
                <strong><?= htmlspecialchars($a['passager']) ?></strong> ➜
                <strong><?= htmlspecialchars($a['chauffeur']) ?></strong><br>
                Note : <?= (int)$a['note'] ?>/5<br>
                “<?= htmlspecialchars($a['commentaire']) ?>”
                <form action="/employe/moderation" method="post" class="inline-btn">
                  <input type="hidden" name="avis_id" value="<?= (int)$a['id'] ?>">
                  <label style="margin-right:.5rem">
                    <input type="checkbox" name="is_problem" value="1"> Problème ?
                  </label>
                  <button name="action" value="valider" type="submit">
                    <i data-lucide="check-circle"></i> Valider
                  </button>
                  <button name="action" value="refuser" type="submit">
                    <i data-lucide="x-circle"></i> Refuser
                  </button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>Aucun avis à modérer.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- voyages signalés -->
    <section>
      <h3 class="toggle-section"><i data-lucide="alert-triangle"></i> Voyages signalés</h3>
      <div class="content">
        <?php if (!empty($problemes)): ?>
          <ul>
            <?php foreach ($problemes as $pb): ?>
              <li>
                <strong>Passager :</strong> <?= htmlspecialchars($pb['passager']) ?> (<?= htmlspecialchars($pb['email_passager']) ?>)<br>
                <strong>Chauffeur :</strong> <?= htmlspecialchars($pb['chauffeur']) ?> (<?= htmlspecialchars($pb['email_chauffeur']) ?>)<br>
                <strong>Trajet :</strong> <?= htmlspecialchars($pb['ville_depart']) ?> → <?= htmlspecialchars($pb['ville_arrivee']) ?><br>
                <strong>Date :</strong> <?= htmlspecialchars($pb['date_depart']) ?><br>
                <strong>Commentaire :</strong> <?= htmlspecialchars($pb['description']) ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>Aucun trajet signalé.</p>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <?php include_once ROOT.'/includes/footer.php'; ?>

  <script>
    document.querySelectorAll('.toggle-section').forEach(h3 => {
      h3.addEventListener('click', () => {
        const c = h3.nextElementSibling;
        c.style.display = (c.style.display === 'block') ? 'none' : 'block';
      });
    });
    lucide.createIcons();
  </script>
</body>
</html>

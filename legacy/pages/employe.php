<?php
session_start();
require_once __DIR__ . '/../../config/db.php';


if (!isset($_SESSION['user']) || !in_array('employe', $_SESSION['user']['roles'])) {
  header('Location: pages/connexion.php');
  exit;
}

// traitement des actions de modération
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avis_id'], $_POST['action'])) {
  $avis_id = intval($_POST['avis_id']);
  $approuve = $_POST['action'] === 'valider' ? 1 : 0;
  $is_problem = isset($_POST['is_problem']) ? 1 : 0;

  $stmt = $pdo->prepare("UPDATE avis SET approuve = ?, is_problem = ? WHERE id = ?");
  $stmt->execute([$approuve, $is_problem, $avis_id]);

  header("Location: employe.php");
  exit;
}

// avis à modérer
$stmt = $pdo->query("SELECT a.id, a.commentaire, a.note, u1.pseudo AS passager, u2.pseudo AS chauffeur
                     FROM avis a
                     JOIN users u1 ON a.passager_id = u1.id
                     JOIN users u2 ON a.conducteur_id = u2.id
                     WHERE a.approuve = 0");
$avis = $stmt->fetchAll();

// avis signalés comme problème
$stmt = $pdo->query("
    SELECT 
        a.id AS avis_id,
        p.pseudo AS passager,
        c.pseudo AS chauffeur,
        p.email AS email_passager,
        c.email AS email_chauffeur,
        t.ville_depart,
        t.ville_arrivee,
        t.date_depart,
        a.commentaire AS description
    FROM avis a
    JOIN trajets t ON a.conducteur_id = t.conducteur_id
    JOIN users p ON a.passager_id = p.id
    JOIN users c ON a.conducteur_id = c.id
    WHERE a.approuve = 1 AND a.is_problem = 1
");
$problemes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Espace Employé</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
    .toggle-section {
      cursor: pointer;
      background: #eef;
      padding: 10px;
      border-radius: 5px;
    }

    .content {
      display: none;
      margin-top: 10px;
    }

    li {
      margin-bottom: 1rem;
    }

    form.inline-btn {
      display: inline;
      margin-left: 10px;
    }
  </style>
</head>

<body>
  <?php include_once __DIR__ . '/../../includes/header.php'; ?>

  <main class="container">
    <h2>Espace Employé</h2>

    <!-- modération des avis -->
    <section>
      <h3 class="toggle-section" <i data-lucide="edit"></i> Modération des avis</h3>
      <div class="content">
        <?php if (count($avis) > 0): ?>
          <ul>
            <?php foreach ($avis as $a): ?>
              <li>
                <strong><?= htmlspecialchars($a['passager']) ?></strong> ➜
                <strong><?= htmlspecialchars($a['chauffeur']) ?></strong><br>
                Note : <?= $a['note'] ?>/5<br>
                "<?= htmlspecialchars($a['commentaire']) ?>"
                <form action="employe.php" method="post" class="inline-btn">
                  <input type="hidden" name="avis_id" value="<?= $a['id'] ?>">
                  <label>
                    <input type="checkbox" name="is_problem" value="1"> Problème ?
                  </label>

                  <button name="action" value="valider">
                    <i data-lucide="check-circle"></i> Valider
                  </button>

                  <button name="action" value="refuser">
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
      <h3 class="toggle-section"> <i data-lucide="alert-triangle"></i> Voyages signalés</h3>
      <div class="content">
        <?php if (count($problemes) > 0): ?>
          <ul>
            <?php foreach ($problemes as $pb): ?>
              <li>
                <strong>Passager :</strong> <?= htmlspecialchars($pb['passager']) ?> (<?= $pb['email_passager'] ?>)<br>
                <strong>Chauffeur :</strong> <?= htmlspecialchars($pb['chauffeur']) ?> (<?= $pb['email_chauffeur'] ?>)<br>
                <strong>Trajet :</strong> <?= htmlspecialchars($pb['ville_depart']) ?> →
                <?= htmlspecialchars($pb['ville_arrivee']) ?><br>

                <strong>Date :</strong> <?= $pb['date_depart'] ?><br>
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

  <script>
    document.querySelectorAll('.toggle-section').forEach(h3 => {
      h3.addEventListener('click', () => {
        const content = h3.nextElementSibling;
        content.style.display = content.style.display === 'block' ? 'none' : 'block';
      });
    });

    lucide.createIcons();

  </script>

  <?php include_once __DIR__ . '/../../includes/footer.php'; ?>
</body>

</html>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
  <?php include_once ROOT . '/includes/header.php'; ?>

  <main>
    <section class="hero">
      <img src="/assets/images/hero.jpg" alt="EcoRide Image de fond">
    </section>

    <div class="admin-container">
      <h2>Bienvenue, Admin</h2>

      <!-- flash -->
      <?php if (!empty($_SESSION['flash_admin'])): ?>
        <div class="alert-success" style="margin-bottom:1rem;">
          <?= htmlspecialchars($_SESSION['flash_admin']); unset($_SESSION['flash_admin']); ?>
        </div>
      <?php endif; ?>

      <!-- section création employé -->
      <section>
        <div class="section-toggle" onclick="toggleSection('createEmp')">
          <i data-lucide="user-plus"></i> Créer un employé
        </div>
        <div id="createEmp" class="section-content">
          <form action="/admin/employes" method="post">
            <label>Pseudo : <input type="text" name="pseudo" required></label><br>
            <label>Email : <input type="email" name="email" required></label><br>
            <label>Mot de passe : <input type="password" name="password" required></label><br>
            <label>Confirmer le mot de passe : <input type="password" name="confirm_password" required></label><br>
            <button type="submit" class="btn">Créer</button>
          </form>
        </div>
      </section>

      <!-- graphique des trajets par jour -->
      <section>
        <div class="section-toggle" onclick="toggleSection('trajetsChartBlock')">
          <i data-lucide="bus"></i> Trajets par jour
        </div>
        <div id="trajetsChartBlock" class="section-content">
          <canvas id="trajetsChart"></canvas>
        </div>
        <script>
          const trajetsLabels = <?= json_encode(array_keys($trajetsData ?? [])) ?>;
          const trajetsValues = <?= json_encode(array_values($trajetsData ?? [])) ?>;
          if (trajetsLabels.length) {
            new Chart(document.getElementById('trajetsChart'), {
              type: 'bar',
              data: {
                labels: trajetsLabels,
                datasets: [{
                  label: 'Trajets par jour',
                  data: trajetsValues,
                  backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
              }
            });
          }
        </script>
      </section>

      <!-- graphique des crédits -->
      <section>
        <div class="section-toggle" onclick="toggleSection('creditsChartBlock')">
          <i data-lucide="bar-chart-2"></i> Crédits gagnés
        </div>
        <div id="creditsChartBlock" class="section-content">
          <h3>Crédits gagnés par jour</h3>
          <canvas id="creditsChart"></canvas>
        </div>
        <script>
          const creditsLabels = <?= json_encode(array_keys($creditsData ?? [])) ?>;
          const creditsValues = <?= json_encode(array_values($creditsData ?? [])) ?>;
          if (creditsLabels.length) {
            new Chart(document.getElementById('creditsChart'), {
              type: 'line',
              data: {
                labels: creditsLabels,
                datasets: [{
                  label: 'Crédits gagnés',
                  data: creditsValues,
                  borderColor: 'rgb(75, 192, 192)',
                  fill: false,
                  tension: 0.3
                }]
              }
            });
          }
        </script>
      </section>

      <!-- statistiques générales -->
      <div class="section-toggle" onclick="toggleSection('statsBlock')">
        <i data-lucide="clipboard-list"></i> Statistiques générales
      </div>
      <div id="statsBlock" class="section-content">
        <p>Total de crédits gagnés par la plateforme (id=1) :
          <strong><?= (int)$platformCredits ?></strong>
        </p>
      </div>

      <!-- suspension de comptes -->
      <div class="section-toggle" onclick="toggleSection('suspendBlock')">
        <i data-lucide="ban"></i> Suspension de comptes
      </div>
      <div id="suspendBlock" class="section-content">
        <table>
          <tr>
            <th>Pseudo</th>
            <th>Email</th>
            <th>Crédits</th>
            <th>Action</th>
          </tr>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['pseudo']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= (int)$u['credits'] ?></td>
              <td>
                <?php if (!$u['is_active']): ?>
                  <span class="btn btn-disabled">Suspendu</span>
                <?php else: ?>
                  <!-- POST на /admin/suspend -->
                  <form action="/admin/suspend" method="post" style="display:inline"
                        onsubmit="return confirm('Suspendre cet utilisateur ?');">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <button type="submit" class="btn-suspendre">Suspendre</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>

    </div>
  </main>

  <?php include_once ROOT . '/includes/footer.php'; ?>

  <script>
    lucide.createIcons();
    function toggleSection(id) {
      const el = document.getElementById(id);
      el.style.display = (el.style.display === 'block') ? 'none' : 'block';
    }
  </script>
</body>
</html>

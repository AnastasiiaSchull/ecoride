<?php
session_start();

require_once '../config/db.php';

// protection : uniquement pour l'administrateur
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT r.nom FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?");
$stmt->execute([$user_id]);
$roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!in_array('admin', $roles)) {
    echo "Accès refusé.";
    exit;
}

// données pour le graphique 1 : trajets par jour
$stmt = $pdo->query("
    SELECT DATE(date_depart) as jour, COUNT(*) as total
    FROM trajets
    GROUP BY jour
    ORDER BY jour ASC
");
$trajetsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [ '2025-07-10' => 3, ... ]

// données pour le graphique 2 : crédits gagnés (2 crédits par réservation pour la plateforme)
$stmt = $pdo->query("
    SELECT DATE(date_reservation) as jour, COUNT(*) * 2 as credits
    FROM reservations
    GROUP BY jour
    ORDER BY jour ASC
");
$creditsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // [ '2025-07-10' => 4, ... ]
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <link rel="stylesheet" href="../public/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

</head>

<body>
    <?php include_once '../includes/header.php'; ?>
    <main>
        <section class="hero">
            <img src="../public/assets/images/hero.jpg" alt="EcoRide Image de fond">
        </section>
        <div class="admin-container">
            <h2>Bienvenue, Admin</h2>

            <!-- section création employé -->
            <section>
                <div class="section-toggle" onclick="toggleSection('createEmp')"><i data-lucide="user-plus"></i> Créer
                    un
                    employé</div>
                <div id="createEmp" class="section-content">
                    <form action="creer_employe.php" method="post">
                        <label>Pseudo : <input type="text" name="pseudo" required></label><br>
                        <label>Email : <input type="email" name="email" required></label><br>
                        <label>Mot de passe : <input type="password" name="password" required></label><br>
                        <label>Confirmer le mot de passe : <input type="password" name="confirm_password"
                                required></label><br>

                        <button type="submit" class="btn">Créer</button>
                    </form>
                </div>


            </section>

            <!-- graphique des trajets par jour -->
            <section>
                <div class="section-toggle" onclick="toggleSection('trajetsChartBlock')"><i data-lucide="bus"></i>
                    Trajets par jour</div>
                <div id="trajetsChartBlock" class="section-content">
                    <canvas id="trajetsChart"></canvas>
                </div>

                <script>
                    const trajetsLabels = <?= json_encode(array_keys($trajetsData)) ?>;
                    const trajetsValues = <?= json_encode(array_values($trajetsData)) ?>;

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
                </script>
            </section>

            <!-- graphique des crédits -->
            <section>
                <div class="section-toggle" onclick="toggleSection('creditsChartBlock')"><i
                        data-lucide="bar-chart-2"></i> Crédits gagnés</div>
                <div id="creditsChartBlock" class="section-content">
                    <h3>Crédits gagnés par jour</h3>
                    <canvas id="creditsChart"></canvas>
                </div>

                <script>
                    const creditsLabels = <?= json_encode(array_keys($creditsData)) ?>;
                    const creditsValues = <?= json_encode(array_values($creditsData)) ?>;

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
                </script>
            </section>
            <!-- statistiques générales -->
            <div class="section-toggle" onclick="toggleSection('statsBlock')"><i data-lucide="clipboard-list"></i>
                Statistiques générales</div>
            <div id="statsBlock" class="section-content">
                <?php
                $stmt = $pdo->query("SELECT credits FROM users WHERE id = 1");
                $platformCredits = $stmt->fetchColumn() ?? 0;
                ?>
                <p>Total de crédits gagnés par la plateforme (id=1) : <strong><?= $platformCredits ?></strong></p>
            </div>

            <!-- suspension -->
            <div class="section-toggle" onclick="toggleSection('suspendBlock')"><i data-lucide="ban"></i> Suspension de
                comptes</div>
            <div id="suspendBlock" class="section-content">
                <table>
                    <tr>
                        <th>Pseudo</th>
                        <th>Email</th>
                        <th>Crédits</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    $users = $pdo->query("SELECT * FROM users WHERE id != $user_id AND pseudo != 'Admin'")->fetchAll();
                    foreach ($users as $u):
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($u['pseudo']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= $u['credits'] ?></td>
                            <td>
                                <?php if (!$u['is_active']): ?>
                                    <span class="btn btn-disabled">Suspendu</span>
                                <?php else: ?>
                                    <a href="suspendre.php?id=<?= $u['id'] ?>" class="btn-suspendre">Suspendre</a>
                                <?php endif; ?>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </main>
    <?php include_once '../includes/footer.php'; ?>
    <script>
        lucide.createIcons();

        function toggleSection(id) {
            const section = document.getElementById(id);
            section.style.display = section.style.display === 'block' ? 'none' : 'block';
        }              
    </script>

</body>

</html>
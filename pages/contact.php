<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Contact - EcoRide</title>
    <link rel="stylesheet" href="../public/assets/css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <main>
        <section class="container" style="margin-top: 2rem; text-align: center;">
            <h3>À propos du développeur</h3>
            <p>Développé par <strong>Anastasiia Schull</strong>, développeuse web en formation chez Studi.</p>
            <p>Compétences : HTML, CSS, JavaScript, PHP, MySQL, Figma, MongoDB, Git, Jira</p>
        </section>

        <div class="container">
            <p style="margin-top: 1rem; color: #888; text-align: center;">
                <strong>Infos de test :</strong><br>
                Admin -> <code>admin@agora.com</code> / <code>admin</code><br>
                Employé -> <code>employe1@gmail.com</code> / <code>motdepasse</code><br>
                User -> <code>lucas.durand@gmail.com</code> / <code>motdepasse</code>
            </p>
        </div>
    </main>
    <?php include '../includes/footer.php'; ?>
</body>

</html>
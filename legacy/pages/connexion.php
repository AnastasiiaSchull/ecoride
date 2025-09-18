<?php session_start();
// La base principale de l'application est MySQL (pour les utilisateurs, trajets, etc.).
// En plus, on utilise MongoDB pour enregistrer les connexions des utilisateurs.
// À chaque connexion, on garde une trace : adresse IP, date, navigateur, etc.
// C’est comme un petit journal pour suivre qui s’est connecté et quand.
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Connexion - EcoRide</title>
    <link rel="stylesheet" href="/assets/css/style.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body>
    <?php include_once __DIR__ . '/../../includes/header.php'; ?>
    <main>
        <h2 class="search" style="margin-top: 1.125rem">Connexion</h2>

        <div class="container" style="margin-top: 1.25rem">
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="warning">
                    <?= htmlspecialchars($_SESSION['flash']) ?>
                </div>
                <?php unset($_SESSION['flash']); ?>
            <?php endif; ?>
            <form method="POST" action="traitement_connexion.php">
                <label for="email">Email :
                    <input type="email" id="email" name="email" required />
                </label>

                <label for="password">Mot de passe :
                    <input type="password" id="password" name="password" required />
                </label>
                <div style="text-align: center; margin-top: 1rem;">
                    <button type="submit" class="btn">Se connecter</button>
                </div>
            </form>
            <div class="div-centre">
                <p style="margin-top: 1rem;">Pas encore inscrit ?
                    <a href="inscription.php" style="margin-left: 1.25rem;">Créer un compte</a>
                </p>
            </div>
        </div>
       
    </main>
     <?php include_once __DIR__ . '/../../includes/footer.php'; ?>

</body>

</html>
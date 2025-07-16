<?php
session_start();
require_once '../config/db.php'; // connexion à la base de données

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    // rechercher l'utilisateur par email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // vérifier s'il est suspendu
    if ($user && !$user['is_active']) {
        $_SESSION['flash'] = "Ce compte a été suspendu.";
        header("Location: connexion.php");
        exit;
    }

    // vérifier l'utilisateur et le mot de passe
    if ($user && password_verify($password, $user['password'])) {
        // récupérer ses rôles
        $stmt = $pdo->prepare("SELECT r.nom FROM roles r 
            INNER JOIN user_roles ur ON r.id = ur.role_id 
            WHERE ur.user_id = ?");
        $stmt->execute([$user['id']]);
        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // stocker les données dans la session
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user']['roles'] = $roles; // tableau de rôles

        header("Location: accueil.php");
        exit;
    } else {
        echo "Email ou mot de passe incorrect.";
    }
}
?>

<?php
session_start();
require_once '../config/db.php';

// protection : uniquement pour l'administrateur
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit;
}

// récupération des données
$pseudo = trim($_POST['pseudo'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if (!$pseudo || !$email || !$password || !$confirm) {
    $_SESSION['flash'] = "Tous les champs sont obligatoires.";
    header('Location: admin.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash'] = "Email invalide.";
    header('Location: admin.php');
    exit;
}

if ($password !== $confirm) {
    $_SESSION['flash'] = "Les mots de passe ne correspondent pas.";
    header('Location: admin.php');
    exit;
}

// vérification de l'email en double
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetchColumn() > 0) {
    $_SESSION['flash'] = "Cet email est déjà utilisé.";
    header('Location: admin.php');
    exit;
}

// création de l'utilisateur
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (pseudo, email, password, created_at, photo, credits, is_active)
                       VALUES (?, ?, ?, NOW(), 'default.png', 0, 1)");
$stmt->execute([$pseudo, $email, $hashed]);

$userId = $pdo->lastInsertId();

// attribution du rôle (employé = role_id 3)
$stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
$stmt->execute([$userId, 3]);

$_SESSION['flash'] = "Employé créé avec succès.";
header('Location: admin.php');
exit;
?>

<?php 
session_start();

require_once __DIR__ . '/../../config/db.php';
 // connexion à la base de données
require_once __DIR__ . '../../../vendor/autoload.php';
 // chargement des bibliothèques (MongoDB incluse)
use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

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
        $_SESSION['user']['roles'] = $roles;

        // -----------------------------
        // Enregistrement de la connexion dans MongoDB
        // On utilise MongoDB juste pour suivre les connexions des utilisateurs :
        // ID utilisateur, IP, navigateur, date et heure
        // -----------------------------
        try {
            // $mongo = new MongoDB\Client("mongodb://localhost:27017");
            // $mongo = new MongoDB\Client("mongodb://mongo:27017");
            $mongo = new MongoDB\Client("mongodb://admin:password@ecoride-mongo.internal:27017");

            $collection = $mongo->ecoride->login_history;

            $collection->insertOne([
                'user_id' => $user['id'],
                'email' => $user['email'],
                'ip' => $_SERVER['REMOTE_ADDR'],
                'browser' => $_SERVER['HTTP_USER_AGENT'],
                'status' => 'success',
                'date' => new MongoDB\BSON\UTCDateTime()
            ]);
        } catch (Exception $e) {
            // En cas d’erreur de connexion à Mongo, on n’interrompt pas la session
            error_log("Erreur MongoDB : " . $e->getMessage());
        }

        header("Location: accueil.php");
        exit;
    } else {
        echo "Email ou mot de passe incorrect.";

        // Tentative de connexion échouée — on peut aussi la logguer dans MongoDB
        try {
            // $mongo = new MongoDB\Client("mongodb://localhost:27017");
            // $mongo = new MongoDB\Client("mongodb://mongo:27017");
            $mongo = new MongoDB\Client("mongodb://admin:password@ecoride-mongo.internal:27017");

            $collection = $mongo->ecoride->login_history;

            $collection->insertOne([
                'email' => $email,
                'ip' => $_SERVER['REMOTE_ADDR'],
                'browser' => $_SERVER['HTTP_USER_AGENT'],
                'status' => 'fail',
                'date' => new MongoDB\BSON\UTCDateTime()
            ]);
        } catch (Exception $e) {
            error_log("Erreur MongoDB (fail log) : " . $e->getMessage());
        }
    }
}
?>

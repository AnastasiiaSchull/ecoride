<?php
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Vehicle.php';

class AuthController
{
    public function __construct(private PDO $pdo) {}

    /** GET /connexion */
    public function loginForm(): void {
        $errors = []; $old = [];
        include __DIR__ . '/../Views/auth/login.php';
    }

    /** POST /connexion */
    public function login(): void {
        $u = new User($this->pdo);
        $errors = []; $old = $_POST;

         $email = mb_strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $errors[] = "Email et mot de passe requis.";
            include __DIR__ . '/../Views/auth/login.php';
            return;
        }

        $user = $u->findByEmail($email);

        // suspendu ?
        if ($user && !$user['is_active']) {
            $errors[] = "Ce compte a été suspendu.";
            include __DIR__ . '/../Views/auth/login.php';
            return;
        }

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = "Identifiants invalides.";
            include __DIR__ . '/../Views/auth/login.php';
            return;
        }

        // OK — session
        // session (+ passerelle pour l'ancien en-tête s'il lit $_SESSION['user'])
        $_SESSION['user_id'] = (int)$user['id'];
         $_SESSION['pseudo']  = $user['pseudo'];
        $_SESSION['roles']   = $u->roles((int)$user['id']);
        $_SESSION['user'] = [
            'id'     => (int)$user['id'],
            'pseudo' => $user['pseudo'],
            'email'  => $user['email'],
            'roles'  => $_SESSION['roles'],
            'photo'  => $user['photo'] ?? 'default.png',
        ];

        // MongoDB local dans Docker
        // fonctionne si l’extension PHP  mongodb  et le paquet Composer mongodb/mongodb  sont installés

        if (class_exists(\MongoDB\Client::class)) {
            try {
                
                $dsn = getenv('MONGO_DSN') ?: 'mongodb://mongo:27017';
                $client = new MongoDB\Client($dsn);

                $client->ecoride->login_history->insertOne([
                    'user_id' => (int)$user['id'],
                    'email'   => $user['email'],
                    'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
                    'agent'   => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'status'  => 'success',
                    'date'    => new MongoDB\BSON\UTCDateTime()
                ]);
            } catch (\Throwable $e) {
                error_log("Mongo log error: ".$e->getMessage());
            }
        }

        header('Location: /'); 
        exit;
    }

    /* GET /inscription — afficher la page d'inscription */
    public function registerForm(): void {
        $errors = [];
        $old = [];
        include __DIR__ . '/../Views/auth/register.php';
    }

    /* POST /inscription — traiter la demande d'inscription */
    public function register(): void {
        $u   = new User($this->pdo);
        $veh = new Vehicle($this->pdo);

        $errors = [];
        $old = $_POST;

        $pseudo = trim($_POST['pseudo'] ?? '');
        $email  = trim($_POST['email']  ?? '');
        $pass   = $_POST['password'] ?? '';
        $pass2  = $_POST['confirm_password'] ?? '';
        $roles  = $_POST['roles'] ?? [];

        if ($pseudo === '' || $email === '' || $pass === '' || $pass2 === '') {
            $errors[] = "Tous les champs obligatoires doivent être remplis.";
        }
        if ($pass !== $pass2) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        if (empty($roles)) {
            $errors[] = "Veuillez sélectionner au moins un rôle.";
        }
        if ($u->findByEmail($email)) {
            $errors[] = "Cet email est déjà utilisé.";
        }

        // si le rôle est conducteur 
        $marque  = $_POST['marque']  ?? null;
        $modele  = $_POST['modele']  ?? null;
        $couleur = $_POST['couleur'] ?? null;
        $energie = $_POST['energie'] ?? null;
        $places  = $_POST['places']  ?? null;

        $needsVehicle = in_array('conducteur', $roles, true);

        if ($needsVehicle) {
            if (!$marque || !$modele || !$couleur || !$energie || !is_numeric($places) || (int)$places <= 0) {
                $errors[] = "Tous les champs du véhicule doivent être remplis correctement.";
            }
        }

        if ($errors) {
            include __DIR__ . '/../Views/auth/register.php';
            return;
        }

        $hash   = password_hash($pass, PASSWORD_DEFAULT);
        $userId = $u->create($pseudo, $email, $hash);

        foreach ($roles as $r) {
            $u->attachRoleByName($userId, $r);
        }

        if ($needsVehicle) {
            $veh->create(
                $userId,
                (string)$marque, (string)$modele, (string)$couleur,
                (string)$energie, (int)$places
            );
        }

        header('Location: /connexion');
        exit;
    }

    /** GET /logout */
    public function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
        }
        session_destroy();
        header('Location: /');
        exit;
    }
}

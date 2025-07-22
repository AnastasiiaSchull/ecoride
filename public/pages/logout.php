<?php
session_start();

// supprimer toutes les variables de session
$_SESSION = [];

// supprimer le cookie de session s'il existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// détruire la session
session_destroy();

header("Location: ../index.php");
exit;


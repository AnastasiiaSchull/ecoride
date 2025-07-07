<?php
require_once 'config/db.php';

if ($pdo) {
    echo "Connexion à la base réussie !";
} else {
    echo "Échec de connexion.";
}
?>

<?php

// $host = getenv('DB_HOST') ?: 'localhost';//xampp
//$host = getenv('DB_HOST') ?: 'mysql';//docker
$host = getenv('DB_HOST') ?: 'ecoride-mysql-proud-waterfall-4846.internal'; // это имя MySQL-приложения на Fly
$dbname = getenv('MYSQL_DATABASE') ?: 'ecoride';
$user = getenv('MYSQL_USER') ?: 'user';// celui défini dans docker-compose.yml
$password = getenv('MYSQL_PASSWORD') ?: 'password';

//echo "Connexion à la base de données: host=$host; db=$dbname; user=$user<br>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    // activer le mode d'affichage des erreurs PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

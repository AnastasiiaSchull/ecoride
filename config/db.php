<?php

// $host = getenv('DB_HOST') ?: 'localhost';//xampp
$host = getenv('DB_HOST') ?: 'mysql';//docker
$port = getenv('DB_PORT') ?: '3306';
//$host = getenv('DB_HOST') ?: 'ecoride-mysql-proud-waterfall-4846.internal';   //fly                                        // это имя MySQL-приложения на Fly
$dbname = getenv('MYSQL_DATABASE') ?: 'ecoride';
$user = getenv('MYSQL_USER') ?: 'user';// celui défini dans docker-compose.yml
$password = getenv('MYSQL_PASSWORD') ?: 'password';



$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

return $pdo;
?>

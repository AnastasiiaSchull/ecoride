<?php
require_once __DIR__ . '/../config/db.php';


try {
    $sql = <<<SQL

SET FOREIGN_KEY_CHECKS=0;

-- DROP TABLE IF EXISTS avis, preferences, reservations, roles, trajets, user_roles, users, vehicule_preferences, vehicules;

CREATE TABLE IF NOT EXISTS  users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pseudo VARCHAR(50) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  credits INT DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  photo VARCHAR(255) DEFAULT 'default.png'
);

CREATE TABLE IF NOT EXISTS roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS user_roles (
  user_id INT NOT NULL,
  role_id INT NOT NULL,
  PRIMARY KEY (user_id, role_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS preferences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS vehicules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  marque VARCHAR(50),
  modele VARCHAR(50),
  couleur VARCHAR(30),
  energie ENUM('electrique','essence','diesel') DEFAULT 'essence',
  places INT,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS vehicule_preferences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicule_id INT NOT NULL,
  preference_id INT NOT NULL,
  FOREIGN KEY (vehicule_id) REFERENCES vehicules(id) ON DELETE CASCADE,
  FOREIGN KEY (preference_id) REFERENCES preferences(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS trajets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  conducteur_id INT,
  vehicule_id INT,
  ville_depart VARCHAR(100),
  ville_arrivee VARCHAR(100),
  date_depart DATETIME,
  date_arrivee DATETIME,
  prix DECIMAL(6,2),
  places_dispo INT,
  eco TINYINT(1) DEFAULT 0,
  statut ENUM('à_venir','en_cours','terminé') DEFAULT 'à_venir',
  FOREIGN KEY (conducteur_id) REFERENCES users(id),
  FOREIGN KEY (vehicule_id) REFERENCES vehicules(id)
);

CREATE TABLE IF NOT EXISTS reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trajet_id INT,
  passager_id INT,
  statut ENUM('en_attente','confirmee','annulee') DEFAULT 'en_attente',
  date_reservation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trajet_id) REFERENCES trajets(id),
  FOREIGN KEY (passager_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS avis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  conducteur_id INT,
  passager_id INT,
  note INT,
  commentaire TEXT,
  approuve TINYINT(1) DEFAULT 0,
  is_problem TINYINT(1) DEFAULT 0,
  FOREIGN KEY (conducteur_id) REFERENCES users(id),
  FOREIGN KEY (passager_id) REFERENCES users(id),
  CHECK (note BETWEEN 1 AND 5)
);



SET FOREIGN_KEY_CHECKS=1;

SQL;

    $pdo->exec($sql);
    echo "<h3 style='color:green;'>La base a été créée et préparée avec succès !</h3>";
} catch (PDOException $e) {
    echo "<h3 style='color:red;'>Error:</h3> " . $e->getMessage();
}
?>


CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pseudo VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('utilisateur', 'conducteur', 'passager', 'employe', 'admin') DEFAULT 'utilisateur',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE vehicules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  marque VARCHAR(50),
  modele VARCHAR(50),
  couleur VARCHAR(30),
  energie ENUM('electrique', 'essence', 'diesel') DEFAULT 'essence',
  places INT,
  FOREIGN KEY (user_id) REFERENCES users(id)
);


CREATE TABLE trajets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  conducteur_id INT,
  vehicule_id INT,
  ville_depart VARCHAR(100),
  ville_arrivee VARCHAR(100),
  date_depart DATETIME,
  date_arrivee DATETIME,
  prix DECIMAL(6,2),
  places_dispo INT,
  eco BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (conducteur_id) REFERENCES users(id),
  FOREIGN KEY (vehicule_id) REFERENCES vehicules(id)
);


CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trajet_id INT,
  passager_id INT,
  statut ENUM('en_attente', 'confirmee', 'annulee') DEFAULT 'en_attente',
  date_reservation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trajet_id) REFERENCES trajets(id),
  FOREIGN KEY (passager_id) REFERENCES users(id)
);


CREATE TABLE avis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  conducteur_id INT,
  passager_id INT,
  note INT CHECK (note BETWEEN 1 AND 5),
  commentaire TEXT,
  approuve BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (conducteur_id) REFERENCES users(id),
  FOREIGN KEY (passager_id) REFERENCES users(id)
);

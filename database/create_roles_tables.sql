-- Création de la table des rôles
CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(50) UNIQUE NOT NULL
);

INSERT INTO roles (nom) VALUES 
('conducteur'),
('passager'),
('employe'),
('admin');

--table pivot pour les rôles des utilisateurs
CREATE TABLE user_roles (
  user_id INT,
  role_id INT,
  PRIMARY KEY (user_id, role_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE preferences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) UNIQUE NOT NULL
);

INSERT INTO preferences (nom) VALUES
('fumeur'),
('animaux'),
('musique'),
('silence'),
('climatisation');

CREATE TABLE vehicule_preferences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicule_id INT NOT NULL,
  preference_id INT NOT NULL,
  FOREIGN KEY (vehicule_id) REFERENCES vehicules(id) ON DELETE CASCADE,
  FOREIGN KEY (preference_id) REFERENCES preferences(id) ON DELETE CASCADE
);


ALTER TABLE users DROP COLUMN role;

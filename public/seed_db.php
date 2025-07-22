<?php
require_once __DIR__ . '/../config/db.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS=0");

    // remplissage de la table des rôles
    $pdo->exec("
        INSERT IGNORE INTO roles (nom) VALUES 
        ('conducteur'), ('passager'), ('employe'), ('admin')
    ");

    // remplissage de la table preferences
    $pdo->exec("
        INSERT IGNORE INTO preferences (nom) VALUES
        ('fumeur'), ('animaux'), ('musique'), ('silence'), ('climatisation')
    ");

    // remplissage de la table users
    $pdo->exec("
        INSERT IGNORE INTO users (id, pseudo, email, password, created_at, credits, is_active, photo) VALUES
        (1, 'Jean Dupont', 'jean@gmail.com', '$2y$10\$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 120, 1, 'default.png'),
        (2, 'Alice Martin', 'alice@gmail.com', '$2y$10\$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 50, 1, 'default.png'),
        (3, 'Luc Moreau', 'luc@gmail.com', '$2y$10\$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 150, 1, 'default.png'),
        (4, 'Emma Lefevre', 'emma@gmail.com', '$2y$10\$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 40, 1, 'default.png'),
        (5, 'Francis Dupont', 'francis@gmail.com', '$2y$10\$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 120, 1, 'default.png'),
        (6, 'Alice Spivak', 'spivak@gmail.com', '$2y$10\$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 50, 1, 'default.png'),
        (7, 'Luc Schull', 'schull@gmail.com', '$2y$10\$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 150, 1, 'default.png'),
        (8, 'Nastia Schull', 'nastia@gmail.com', '$2y$10\$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 40, 1, 'default.png'),
        (9, 'Employé1', 'employe1@gmail.com', '$2y$10\$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 0, 1, 'default.png'),
        (10, 'Employé2', 'employe2@gmail.com', '$2y$10\$hdLRgJyBi/iJAzVFEauyE.wrrZ4QMrAgcDR0mLcnP6ZAQErfteM0i', NOW(), 0, 1, 'default.png')
    ");


    $pdo->exec("
        INSERT IGNORE INTO user_roles (user_id, role_id) VALUES
        (1, 2), (2, 2), (3, 1), (4, 1), (5, 1), (6, 2), (7, 2), (8, 2),
        (6, 1), (7, 1), (9, 3), (10, 3)
    ");


    $pdo->exec("
        INSERT INTO vehicules (user_id, marque, modele, couleur, energie, places) VALUES
        (6, 'Toyota', 'Yaris', 'rouge', 'essence', 4),
        (6, 'Tesla', 'Model 3', 'noir', 'electrique', 5),
        (7, 'Peugeot', '208', 'gris', 'diesel', 4),
        (7, 'Renault', 'ZOE', 'bleu', 'electrique', 4),
        (8, 'Volkswagen', 'Golf', 'blanc', 'essence', 4),
        (8, 'Nissan', 'Leaf', 'vert', 'electrique', 5),
        (8, 'Peugeot', '308', 'noir', 'essence', 4),
        (2, 'Citroën', 'C4', 'bleu', 'essence', 4),
        (1, 'Renault', 'Clio', 'gris', 'electrique', 4)
    ");


    $pdo->exec("
        INSERT INTO vehicule_preferences (vehicule_id, preference_id) VALUES
        (1, 1), (1, 3), (2, 2), (2, 5), (3, 4), (4, 5), (5, 1), (6, 2),
        (7, 3), (8, 4), (9, 5), (9, 1)
    ");


    $pdo->exec("
        INSERT INTO trajets (conducteur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, date_arrivee, prix, places_dispo, eco, statut) VALUES
        (1, 2, 'Paris', 'Lyon', '2025-12-05 10:00:00', '2025-12-05 14:00:00', 25.00, 3, 1, 'à_venir'),
        (2, 9, 'Paris', 'Lyon', '2025-12-12 14:30:00', '2025-12-12 18:00:00', 28.00, 2, 0, 'à_venir'),
        (8, 1, 'Paris', 'Lyon', '2025-12-19 09:00:00', '2025-12-19 13:00:00', 22.00, 4, 0, 'à_venir'),

        (8, 7, 'Lille', 'Paris', '2025-12-03 08:00:00', '2025-12-03 12:00:00', 20.00, 3, 1, 'à_venir'),
        (8, 8, 'Lille', 'Paris', '2025-12-10 11:00:00', '2025-12-10 15:00:00', 23.00, 2, 0, 'à_venir'),
        (7, 5, 'Lille', 'Paris', '2025-12-17 13:00:00', '2025-12-17 17:00:00', 25.00, 3, 1, 'à_venir'),

        (7, 6, 'Lyon', 'Marseille', '2025-12-06 15:00:00', '2025-12-06 19:00:00', 30.00, 2, 0, 'à_venir'),
        (6, 4, 'Lyon', 'Marseille', '2025-12-13 10:00:00', '2025-12-13 14:00:00', 32.00, 3, 1, 'à_venir'),
        (6, 3, 'Lyon', 'Marseille', '2025-12-20 16:30:00', '2025-12-20 20:30:00', 28.00, 2, 0, 'à_venir'),

        (1, 2, 'Lille', 'Paris', '2025-06-03 08:00:00', '2025-06-03 12:00:00', 25.00, 3, 0, 'terminé'),
        (1, 2, 'Paris', 'Lyon', '2025-06-04 09:30:00', '2025-06-04 14:00:00', 30.00, 4, 1, 'terminé'),
        (3, 9, 'Reims', 'Paris', '2025-06-12 09:00:00', '2025-06-12 11:00:00', 12.00, 1, 1, 'terminé'),
        (2, 9, 'Lyon', 'Marseille', '2025-06-05 07:00:00', '2025-06-05 11:30:00', 28.50, 2, 0, 'terminé'),
        (2, 9, 'Paris', 'Bordeaux', '2025-06-06 10:00:00', '2025-06-06 16:00:00', 35.00, 3, 1, 'terminé'),
        (4, 8, 'Toulouse', 'Nice', '2025-06-07 06:00:00', '2025-06-07 13:00:00', 40.00, 1, 0, 'terminé'),
        (4, 7, 'Marseille', 'Lille', '2025-06-08 09:00:00', '2025-06-08 19:00:00', 50.00, 4, 1, 'terminé'),
        (3, 9, 'Nantes', 'Strasbourg', '2025-06-09 08:00:00', '2025-06-09 18:00:00', 45.00, 2, 0, 'terminé'),
        (1, 2, 'Lyon', 'Reims', '2025-06-10 07:30:00', '2025-06-10 11:00:00', 22.00, 3, 1, 'terminé'),
        (2, 9, 'Paris', 'Toulouse', '2025-06-11 08:00:00', '2025-06-11 14:30:00', 38.00, 2, 1, 'terminé')
    ");


    echo "<h3 style='color:green;'>Данные успешно добавлены!</h3>";
} catch (PDOException $e) {
    echo "<h3 style='color:red;'>Ошибка:</h3> " . $e->getMessage();
}

INSERT INTO trajets (conducteur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, date_arrivee, prix, places_dispo, eco) VALUES
(1, 1, 'Lille', 'Paris', '2025-08-03 08:00:00', '2025-08-03 12:00:00', 25.00, 3, FALSE),
(1, 2, 'Paris', 'Lyon', '2025-08-04 09:30:00', '2025-08-04 14:00:00', 30.00, 4, TRUE),
(3, 3, 'Reims', 'Paris', '2025-07-12 09:00:00', '2025-07-12 11:00:00', 12.00, 1, true),
(2, 1, 'Lyon', 'Marseille', '2025-08-05 07:00:00', '2025-08-05 11:30:00', 28.50, 2, FALSE),
(2, 2, 'Paris', 'Bordeaux', '2025-08-06 10:00:00', '2025-08-06 16:00:00', 35.00, 3, TRUE),
(4, 3, 'Toulouse', 'Nice', '2025-08-07 06:00:00', '2025-08-07 13:00:00', 40.00, 1, FALSE),
(4, 1, 'Marseille', 'Lille', '2025-08-08 09:00:00', '2025-08-08 19:00:00', 50.00, 4, TRUE),
(3, 2, 'Nantes', 'Strasbourg', '2025-08-09 08:00:00', '2025-08-09 18:00:00', 45.00, 2, FALSE),
(1, 3, 'Lyon', 'Reims', '2025-08-10 07:30:00', '2025-08-10 11:00:00', 22.00, 3, TRUE),
(2, 2, 'Paris', 'Toulouse', '2025-08-11 08:00:00', '2025-08-11 14:30:00', 38.00, 2, TRUE);


INSERT INTO vehicules (user_id, marque, modele, couleur, energie, places)
VALUES 
(8, 'Peugeot', '308', 'noir', 'essence', 4),
(9, 'Renault', 'Clio', 'gris', 'electrique', 4);


INSERT INTO trajets (conducteur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, date_arrivee, prix, places_dispo, eco)
VALUES 

(6, 2, 'Paris', 'Lyon', '2025-08-04 09:30:00', '2025-08-04 14:00:00', 30.00, 4, 1),
(7, 3, 'Paris', 'Lyon', '2025-08-04 10:00:00', '2025-08-04 14:00:00', 22.00, 2, 0),
(8, 4, 'Paris', 'Lyon', '2025-08-04 07:00:00', '2025-08-04 11:00:00', 35.00, 3, 1),
(9, 5, 'Paris', 'Lyon', '2025-08-04 13:00:00', '2025-08-04 17:30:00', 20.00, 1, 0),
(10, 6, 'Paris', 'Lyon', '2025-08-04 15:00:00', '2025-08-04 20:00:00', 28.00, 2, 1);


INSERT INTO avis (conducteur_id, passager_id, note, commentaire, approuve) VALUES
(6, 2, 5, 'Excellent conducteur !', 1),
(7, 2, 4, 'Sympa et ponctuel', 1),
(8, 2, 3, 'Trajet correct', 1),
(9, 2, 2, 'Conduite un peu rapide', 1),
(10, 2, 1, 'Retard au départ', 1);
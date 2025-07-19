USE ecoride;

INSERT INTO trajets (conducteur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, date_arrivee, prix, places_dispo, eco, statut)
VALUES
(1, 2, 'Paris', 'Lyon', '2025-12-05 10:00:00', '2025-12-05 14:00:00', 25.00, 3, 1, 'à_venir'),
(1, 3, 'Paris', 'Lyon', '2025-12-12 14:30:00', '2025-12-12 18:00:00', 28.00, 2, 1, 'à_venir'),
(1, 4, 'Paris', 'Lyon', '2025-12-19 09:00:00', '2025-12-19 13:00:00', 22.00, 4, 0, 'à_venir');


INSERT INTO trajets (conducteur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, date_arrivee, prix, places_dispo, eco, statut)
VALUES
(2, 1, 'Lille', 'Paris', '2025-12-03 08:00:00', '2025-12-03 12:00:00', 20.00, 3, 1, 'à_venir'),
(2, 3, 'Lille', 'Paris', '2025-12-10 11:00:00', '2025-12-10 15:00:00', 23.00, 2, 0, 'à_venir'),
(2, 4, 'Lille', 'Paris', '2025-12-17 13:00:00', '2025-12-17 17:00:00', 25.00, 3, 1, 'à_venir');


INSERT INTO trajets (conducteur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, date_arrivee, prix, places_dispo, eco, statut)
VALUES
(3, 1, 'Lyon', 'Marseille', '2025-12-06 15:00:00', '2025-12-06 19:00:00', 30.00, 2, 1, 'à_venir'),
(3, 2, 'Lyon', 'Marseille', '2025-12-13 10:00:00', '2025-12-13 14:00:00', 32.00, 3, 0, 'à_venir'),
(3, 4, 'Lyon', 'Marseille', '2025-12-20 16:30:00', '2025-12-20 20:30:00', 28.00, 2, 1, 'à_venir');



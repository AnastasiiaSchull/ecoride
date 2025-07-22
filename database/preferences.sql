INSERT INTO preferences (nom) VALUES
('fumeur'),
('animaux'),
('musique'),
('silence'),
('climatisation');

INSERT INTO vehicule_preferences (vehicule_id, preference_id) VALUES
(1, 1), -- fumeur
(1, 3), -- musique
(2, 2), -- animaux
(2, 5), -- climatisation
(9, 4), -- silence
(10, 5), -- climatisation
(11, 1), -- fumeur
(12, 2), -- animaux
(13, 3), -- musique
(14, 4), -- silence
(15, 5), -- climatisation
(15, 1); -- fumeur

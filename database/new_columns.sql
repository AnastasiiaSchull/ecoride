ALTER TABLE users ADD COLUMN credits INT DEFAULT 0;

ALTER TABLE trajets ADD COLUMN statut ENUM('à_venir', 'en_cours', 'terminé') DEFAULT 'à_venir';

ALTER TABLE users ADD COLUMN is_active BOOLEAN NOT NULL DEFAULT TRUE;

ALTER TABLE avis ADD COLUMN is_problem BOOLEAN DEFAULT 0;

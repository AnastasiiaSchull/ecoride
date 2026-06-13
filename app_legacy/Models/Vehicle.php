<?php
require_once __DIR__ . '/BaseModel.php';

class Vehicle extends BaseModel {
    //сréer le véhicule du conducteur et renvoyer son id
    public function create(int $userId, string $marque, string $modele, string $couleur, string $energie, int $places): int {
        $this->exec(
            "INSERT INTO vehicules (user_id, marque, modele, couleur, energie, places)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$userId, $marque, $modele, $couleur, $energie, $places]
        );
        return (int)$this->pdo->lastInsertId();
    }

    // 1-er véhicule de l’utilisateur (le cas échéant) 
    public function byUser(int $userId): ?array {
        return $this->row("SELECT * FROM vehicules WHERE user_id = ? LIMIT 1", [$userId]);
    }

    //trouver le véhicule par id
    public function find(int $id): ?array {
        return $this->row("SELECT * FROM vehicules WHERE id = ?", [$id]);
    }

    //référentiel des préférences liées au véhicule
    public function preferences(int $vehicule_id): array {
        $sql = "SELECT p.nom
                FROM preferences p
                JOIN vehicule_preferences vp ON p.id = vp.preference_id
                WHERE vp.vehicule_id = ?";
        return $this->col($sql, [$vehicule_id]);
    }
}

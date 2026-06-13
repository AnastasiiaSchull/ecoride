<?php
require_once __DIR__.'/BaseModel.php';

class Avis extends BaseModel {
    public function create(int $conducteurId, int $passagerId, int $note, string $commentaire): int {
        $this->exec("INSERT INTO avis (conducteur_id, passager_id, note, commentaire) VALUES (?, ?, ?, ?)",
                    [$conducteurId,$passagerId,$note,$commentaire]);
        return (int)$this->pdo->lastInsertId();
    }

/*vérifie si un avis existe déjà pour ce duo conducteur/passager */
    public function exists(int $conducteurId, int $passagerId): bool {
        return (bool)$this->val(
            "SELECT 1 FROM avis WHERE conducteur_id=? AND passager_id=?",
            [$conducteurId, $passagerId]
        );
    }
}

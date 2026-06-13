<?php
require_once __DIR__.'/BaseModel.php';

class Reservation extends BaseModel {
    private const TABLE = 'reservations';

    //créer la réservation et diminuer le nombre de places dans une transaction
    public function create(int $trajetId, int $passagerId): int {
        $this->pdo->beginTransaction();
        try {
            // on tente de prendre une place
            $st = $this->pdo->prepare(
                "UPDATE trajets
                 SET places_dispo = places_dispo - 1
                 WHERE id = ? AND places_dispo > 0"
            );
            $st->execute([$trajetId]);
            if ($st->rowCount() !== 1) {
                throw new RuntimeException("Plus de places.");
            }

            //  on insère la réservation
            $this->exec(
                "INSERT INTO ".self::TABLE." (trajet_id, passager_id, statut)
                 VALUES (?, ?, 'en_attente')",
                [$trajetId, $passagerId]
            );
            $id = (int)$this->pdo->lastInsertId();

            $this->pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    //confirmer la réservation : statut 'en_attente' → 'confirmee' 
    public function confirm(int $reservationId): bool {
        return $this->exec(
            "UPDATE ".self::TABLE." SET statut='confirmee'
             WHERE id=? AND statut='en_attente'",
            [$reservationId]
        ) === 1;
    }

    
    public function cancel(int $reservationId, int $userId): bool {
        $this->pdo->beginTransaction();
        try {
            $row = $this->row(
                "SELECT trajet_id, statut
                 FROM ".self::TABLE."
                 WHERE id=? AND passager_id=?",
                [$reservationId, $userId]
            );
            if (!$row || $row['statut'] === 'annulee') {
                $this->pdo->rollBack();
                return false;
            }

            $this->exec(
                "UPDATE ".self::TABLE." SET statut='annulee' WHERE id=?",
                [$reservationId]
            );
            $this->exec(
                "UPDATE trajets SET places_dispo = places_dispo + 1 WHERE id=?",
                [$row['trajet_id']]
            );

            $this->pdo->commit();
            return true;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    //réservations de l’utilisateur — aperçu du trajet
    public function byUser(int $userId): array {
        $sql = "SELECT r.*,
                       t.ville_depart, t.ville_arrivee, t.date_depart, t.date_arrivee, t.prix
                FROM ".self::TABLE." r
                JOIN trajets t ON t.id = r.trajet_id
                WHERE r.passager_id = ?
                ORDER BY r.date_reservation DESC";
        return $this->all($sql, [$userId]);
    }

    //liste des réservations pour un trajet donné  
    public function byTrajet(int $trajetId): array {
        $sql = "SELECT r.*, u.pseudo AS passager
                FROM ".self::TABLE." r
                JOIN users u ON u.id = r.passager_id
                WHERE r.trajet_id = ?
                ORDER BY r.date_reservation DESC";
        return $this->all($sql, [$trajetId]);
    }

  public function withTripsByUser(int $userId): array {
    $sql = "
        SELECT
            r.id,
            r.statut AS statut,
            r.passager_id,
            t.conducteur_id,
            t.ville_depart, t.ville_arrivee, t.date_depart,
            CASE
              WHEN t.date_depart < NOW()
               AND r.statut <> 'annulee'
               AND NOT EXISTS (
                    SELECT 1 FROM avis a
                    WHERE a.conducteur_id = t.conducteur_id
                      AND a.passager_id   = r.passager_id
                )
              THEN 1 ELSE 0
            END AS peut_avis
        FROM reservations r
        JOIN trajets t ON t.id = r.trajet_id
        WHERE r.passager_id = ?
        ORDER BY COALESCE(t.date_depart, r.date_reservation) DESC
    ";
    $st = $this->pdo->prepare($sql);
    $st->execute([$userId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

public function createConfirmed(int $trajetId, int $passagerId): int {
        $this->exec("INSERT INTO reservations (trajet_id, passager_id, statut)
                     VALUES (?, ?, 'confirmee')", [$trajetId, $passagerId]);
        return (int)$this->pdo->lastInsertId();
    }
   
    // retourne la liste des conducteurs que le passager peut encore évaluer
public function driversForReview(int $passagerId): array {
    $sql = "
        SELECT DISTINCT u.id, u.pseudo
        FROM users u
        JOIN trajets t       ON t.conducteur_id = u.id
        JOIN reservations r  ON r.trajet_id     = t.id
        LEFT JOIN avis a     ON a.conducteur_id = u.id
                             AND a.passager_id  = r.passager_id
        WHERE r.passager_id = ?
          AND r.statut = 'confirmee'        -- réservation validée
          AND t.date_depart < NOW()         -- trajet déjà passé
          AND a.id IS NULL                  -- pas encore d'avis
        ORDER BY u.pseudo ASC
    ";
    $st = $this->pdo->prepare($sql);
    $st->execute([$passagerId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}

}


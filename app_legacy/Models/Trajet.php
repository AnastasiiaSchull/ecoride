<?php
require_once __DIR__ . '/BaseModel.php';

class Trajet extends BaseModel {

    // villes départ  
    public function distinctDeparts(): array {
        return $this->col("SELECT DISTINCT ville_depart FROM trajets ORDER BY ville_depart ASC");
    }

    //villes arrivée 
    public function distinctArrivees(): array {
        return $this->col("SELECT DISTINCT ville_arrivee FROM trajets ORDER BY ville_arrivee ASC");
    }

    /*le premier trajet en base (par défaut)*/
    public function firstRoute(): ?array {
        return $this->row("SELECT id, ville_depart, ville_arrivee FROM trajets ORDER BY id ASC LIMIT 1");
    }

    //top 3 des trajets à venir (page d’accueil)
    public function upcomingShort(int $limit = 3): array {
        $sql = "SELECT ville_depart, ville_arrivee, MIN(date_depart) AS prochaine_date
                FROM trajets
                WHERE date_depart >= CURDATE()
                GROUP BY ville_depart, ville_arrivee
                ORDER BY prochaine_date ASC
                LIMIT ?";
        $st = $this->pdo->prepare($sql);
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchAdvanced(
        ?string $depart,
        ?string $destination,
        ?string $date,
        ?int $passager,
        string $filtre
    ): array {
        $where  = ["DATE(t.date_depart) >= CURDATE()"];
        $params = [];

        if ($depart)      { $where[] = "t.ville_depart = ?";  $params[] = $depart; }
        if ($destination) { $where[] = "t.ville_arrivee = ?"; $params[] = $destination; }
        if ($passager)    { $where[] = "t.places_dispo >= ?"; $params[] = $passager; }

        $sql = "SELECT
                    t.*,
                    u.pseudo AS conducteur_nom,
                    ROUND(n.note_moyenne,1) AS conducteur_note
                FROM trajets t
                JOIN users u ON t.conducteur_id = u.id
                LEFT JOIN (
                    SELECT conducteur_id, AVG(note) AS note_moyenne
                    FROM avis
                    WHERE approuve = 1
                    GROUP BY conducteur_id
                ) n ON t.conducteur_id = n.conducteur_id
                WHERE " . implode(' AND ', $where);

        // filtres
        if ($filtre === 'electrique') {
            $sql .= " AND t.eco = 1";
        } elseif ($filtre === 'prixmin') {
            $sql .= " AND t.prix = (
                SELECT MIN(prix) FROM trajets
                WHERE ville_depart = ? AND ville_arrivee = ?
                  AND DATE(date_depart) >= CURDATE()
                  AND places_dispo >= ?
            )";
            array_push($params, $depart, $destination, $passager ?? 1);
        } elseif ($filtre === 'prixmax') {
            $sql .= " AND t.prix = (
                SELECT MAX(prix) FROM trajets
                WHERE ville_depart = ? AND ville_arrivee = ?
                  AND DATE(date_depart) >= CURDATE()
                  AND places_dispo >= ?
            )";
            array_push($params, $depart, $destination, $passager ?? 1);
        }

        $orderBy = match ($filtre) {
            'prix'   => 't.prix ASC',
            'duree'  => 'TIMESTAMPDIFF(MINUTE, t.date_depart, t.date_arrivee) ASC',
            'note'   => 'conducteur_note DESC',
            default  => 't.eco DESC'
        };
        $sql .= " ORDER BY $orderBy";

        $rows = $this->all($sql, $params);
        if (!$rows) return [];

        $ids   = array_values(array_unique(array_map(fn($r) => (int)$r['conducteur_id'], $rows)));
        $notes = $this->notesForConducteurs($ids);

        foreach ($rows as &$r) {
            $cid = (int)$r['conducteur_id'];
            $r['notes'] = $notes[$cid] ?? [];
        }
        unset($r);
        return $rows;
    }

    public function notesForConducteurs(array $ids): array {
        if (!$ids) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $st = $this->pdo->prepare(
            "SELECT conducteur_id, note
             FROM avis
             WHERE approuve = 1 AND conducteur_id IN ($placeholders)"
        );
        $st->execute($ids);
        $grouped = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $grouped[(int)$row['conducteur_id']][] = (int)$row['note'];
        }
        return $grouped;
    }


    public function create(
        int $conducteur_id,
        int $vehicule_id,
        string $ville_depart,
        string $ville_arrivee,
        string $date_depart,
        string $date_arrivee,
        float $prix,
        int $places_dispo,
        int $eco,
        string $statut = 'à_venir'
    ): int {
        $sql = "INSERT INTO trajets
                    (conducteur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, date_arrivee, prix, places_dispo, eco, statut)
                VALUES (?,?,?,?,?,?,?,?,?,?)";
        $this->exec($sql, [
            $conducteur_id, $vehicule_id, $ville_depart, $ville_arrivee,
            $date_depart, $date_arrivee, $prix, $places_dispo, $eco, $statut
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findDetails(int $id): ?array {
    $sql = "
      SELECT t.*,
             u.pseudo, u.photo,
             v.marque, v.modele, v.energie,
             ROUND(AVG(a.note),1) AS note_moyenne,
             GROUP_CONCAT(a.commentaire SEPARATOR '||') AS commentaires
      FROM trajets t
      JOIN users u ON t.conducteur_id = u.id
      LEFT JOIN vehicules v ON t.vehicule_id = v.id
      LEFT JOIN avis a ON a.conducteur_id = u.id AND a.approuve = 1
      WHERE t.id = ?
      GROUP BY t.id
    ";
    return $this->row($sql, [$id]);
}

public function destinationsForDepart(string $depart): array {
    return $this->col("SELECT DISTINCT ville_arrivee FROM trajets WHERE ville_depart = ?",
                      [$depart]);
}

public function departsForDestination(string $destination): array {
    return $this->col("SELECT DISTINCT ville_depart FROM trajets WHERE ville_arrivee = ?",
                      [$destination]);
}

public function datesBetween(string $a, string $b, string $type): array {
    if ($type === 'depart') {
        $sql = "SELECT DISTINCT DATE(date_depart) 
                FROM trajets WHERE ville_depart=? AND ville_arrivee=? ORDER BY 1";
        return $this->col($sql, [$a,$b]);
    } else {
        $sql = "SELECT DISTINCT DATE(date_depart) 
                FROM trajets WHERE ville_arrivee=? AND ville_depart=? ORDER BY 1";
        return $this->col($sql, [$a,$b]);
    }
}

public function maxPlacesFor(string $depart, string $arrivee, string $date): int {
    $v = $this->val("SELECT MAX(places_dispo) 
                     FROM trajets 
                     WHERE ville_depart=? AND ville_arrivee=? AND DATE(date_depart)=?",
                     [$depart,$arrivee,$date]);
    return (int)($v ?: 0);
}


public function byConducteur(int $userId): array {
  return $this->all("SELECT * FROM trajets WHERE conducteur_id=? ORDER BY date_depart DESC", [$userId]);
}
public function changeStatus(int $trajetId, string $statut): int {
  return $this->exec("UPDATE trajets SET statut=? WHERE id=?", [$statut, $trajetId]);
}
public function belongsToUser(int $trajetId, int $userId): bool {
  return (bool)$this->val("SELECT COUNT(*) FROM trajets WHERE id=? AND conducteur_id=?", [$trajetId,$userId]);
}
public function countConfirmedReservations(int $trajetId): int {
  return (int)$this->val("SELECT COUNT(*) FROM reservations WHERE trajet_id=? AND statut='confirmee'", [$trajetId]);
}

 public function findForUpdate(int $id): ?array {
        return $this->row("SELECT * FROM trajets WHERE id=? FOR UPDATE", [$id]);
    }
public function decrementSeat(int $id): bool {
    $st = $this->pdo->prepare("UPDATE trajets SET places_dispo = places_dispo - 1 WHERE id=? AND places_dispo>0");
    $st->execute([$id]);
    return $st->rowCount() === 1;
}
}


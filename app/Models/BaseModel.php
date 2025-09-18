<?php
declare(strict_types=1);

/**
 * Classe de base pour les modèles (accès PDO simplifié).
 * Fournit des helpers pour lire une ligne, plusieurs lignes,
 * une colonne, une valeur scalaire, exécuter des DML, etc.
 */
abstract class BaseModel {
    public function __construct(protected PDO $pdo) {}

    //retourne toutes les lignes d'une requête sous forme de tableau associatif.
     
    protected function all(string $sql, array $p = []): array {
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // retourne une seule ligne (tableau associatif) ou null si rien.
     
    protected function row(string $sql, array $p = []): ?array {
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        $r = $st->fetch(PDO::FETCH_ASSOC);
        return $r === false ? null : $r;
    }

    // retourne une colonne (liste des valeurs du premier champ).
     
    protected function col(string $sql, array $p = []): array {
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        return $st->fetchAll(PDO::FETCH_COLUMN);
    }

   // retourne une valeur scalaire (première colonne de la première ligne)
     
    protected function val(string $sql, array $p = []): mixed {
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        return $st->fetchColumn();
    }

     // exécute une requête de modification (INSERT/UPDATE/DELETE)
     // return nombre de lignes affectées
     
    protected function exec(string $sql, array $p = []): int {
        $st = $this->pdo->prepare($sql);
        $st->execute($p);
        return $st->rowCount();
    }

    // retourne l'identifiant auto-incrémenté de la dernière insertion
     
    protected function lastId(): string {
        return $this->pdo->lastInsertId();
    }

}

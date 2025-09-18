<?php
require_once __DIR__.'/BaseModel.php';

class User extends BaseModel {
     public function findByEmail(string $email): ?array {
        return $this->row("SELECT * FROM users WHERE email = ?", [$email]);
    }
    public function emailExists(string $email): bool {
        $st = $this->pdo->prepare("SELECT 1 FROM users WHERE email=?");
        $st->execute([$email]); return (bool)$st->fetchColumn();
    }
    public function create(string $pseudo, string $email, string $hash): int {
        $sql = "INSERT INTO users (pseudo, email, password, created_at, is_active, photo)
                VALUES (?, ?, ?, NOW(), 1, 'default.png')";
        $this->exec($sql, [$pseudo, $email, $hash]);
        return (int)$this->lastId();
    }

    public function attachRoleByName(int $userId, string $roleName): void {
        $roleId = (int)$this->val("SELECT id FROM roles WHERE nom = ?", [$roleName]);
        if ($roleId) {
            $this->exec("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$userId, $roleId]);
        }
    }
    
    public function roles(int $userId): array {
        $sql = "SELECT r.nom 
                FROM roles r 
                JOIN user_roles ur ON ur.role_id=r.id 
                WHERE ur.user_id=?";
        return $this->col($sql, [$userId]);
    }

    public function setPhoto(int $id, string $file): void {
    $this->exec("UPDATE users SET photo=? WHERE id=?", [$file,$id]);
}

//retourne le solde de crédits de l'utilisateu
    public function credits(int $userId): int {
        $st = $this->pdo->prepare("SELECT credits FROM users WHERE id=?");
        $st->execute([$userId]);
        return (int)($st->fetchColumn() ?? 0);
    }

    /**
     * Ajuste le solde de crédits (delta peut être négatif ou positif).
     * Empêche de passer en négatif si delta < 0.
     * Retourne true si une ligne a été mise à jour.
     */
    public function adjustCredits(int $userId, int $delta): bool {
        if ($delta >= 0) {
            $st = $this->pdo->prepare("UPDATE users SET credits = credits + ? WHERE id = ?");
            $st->execute([$delta, $userId]);
        } else {
            // evite un solde négatif : on met à jour uniquement si credits >= |delta|
            $st = $this->pdo->prepare("
                UPDATE users
                SET credits = credits + ?
                WHERE id = ? AND credits >= ?
            ");
            $st->execute([$delta, $userId, -$delta]);
        }
        return $st->rowCount() === 1;
    }
}

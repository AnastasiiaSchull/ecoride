<?php
require_once __DIR__.'/BaseModel.php';

class Role extends BaseModel {
    public function idByName(string $name): ?int {
        $st = $this->pdo->prepare("SELECT id FROM roles WHERE nom=?");
        $st->execute([$name]); $id = $st->fetchColumn();
        return $id ? (int)$id : null;
    }
    public function attachToUser(int $userId, int $roleId): void {
        $this->exec("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)", [$userId, $roleId]);
    }
}

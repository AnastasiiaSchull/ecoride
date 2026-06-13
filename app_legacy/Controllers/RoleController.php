<?php
require_once __DIR__.'/../Models/Role.php';
require_once __DIR__.'/../Models/User.php';

class RoleController {
    public function __construct(private PDO $pdo) {}

   /** GET /roles/edit */
    public function edit(): void {
        if (empty($_SESSION['user_id'])) { header('Location: /connexion'); exit; }
        $userId = (int)$_SESSION['user_id'];

        $allowed = ['conducteur','passager'];

        $u = new User($this->pdo);
        $current = $u->roles($userId); // ['conducteur','passager', ...]

        $all = [
            ['nom' => 'conducteur', 'label' => 'Conducteur'],
            ['nom' => 'passager',   'label' => 'Passager'],
        ];

        include ROOT.'/app/Views/roles/edit.php';
    }

    /** POST /roles */
    public function update(): void {
        if (empty($_SESSION['user_id'])) { header('Location: /connexion'); exit; }

        $userId   = (int)$_SESSION['user_id'];
        $allowed  = ['conducteur','passager']; // seuls ces champs peuvent être modifiés par l’utilisateur
        $selected = array_values(array_unique(array_filter((array)($_POST['roles'] ?? []))));
        //conserver uniquement les champs autorisés
        $selected = array_values(array_intersect($allowed, $selected));

        try {
            $this->pdo->beginTransaction();

            // supprimer uniquement les rôles modifiables ; ne jamais toucher aux rôles admin/employe
            $place = implode(',', array_fill(0, count($allowed), '?')); 
            $del = $this->pdo->prepare("
                DELETE ur FROM user_roles ur
                JOIN roles r ON r.id = ur.role_id
                WHERE ur.user_id = ? AND r.nom IN ($place)
            ");
            $del->execute(array_merge([$userId], $allowed));

            // insérer la sélection
            if ($selected) {
                $sel = $this->pdo->prepare(
                    "SELECT id, nom FROM roles WHERE nom IN (".implode(',', array_fill(0, count($selected), '?')).")"
                );
                $sel->execute($selected);
                $rows = $sel->fetchAll(PDO::FETCH_ASSOC);

                $ins = $this->pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
                foreach ($rows as $r) {
                    $ins->execute([$userId, (int)$r['id']]);
                }
            }


            $st = $this->pdo->prepare("SELECT credits FROM users WHERE id=?");
            $st->execute([$userId]);
            $credits = (int)$st->fetchColumn();
            if (in_array('passager', $selected, true) && $credits === 0) {
                $upd = $this->pdo->prepare("UPDATE users SET credits=5 WHERE id=?");
                $upd->execute([$userId]);
            }

            $this->pdo->commit();
            $_SESSION['flash'] = "Rôles mis à jour.";
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            $_SESSION['flash'] = "Erreur: ".$e->getMessage();
        }

        header('Location: /mon_espace'); exit;
    }
}
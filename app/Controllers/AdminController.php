<?php
class AdminController {
    public function __construct(private PDO $pdo) {}

    private function requireAdmin(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $st = $this->pdo->prepare("
            SELECT 1 FROM user_roles ur
            JOIN roles r ON r.id = ur.role_id
            WHERE ur.user_id = ? AND r.nom = 'admin' LIMIT 1
        ");
        $st->execute([ (int)$_SESSION['user_id'] ]);
        if (!$st->fetchColumn()) {
            http_response_code(403);
            echo "Accès refusé."; exit;
        }
    }
    
     private function roleIdByName(string $name): ?int {
        $st = $this->pdo->prepare("SELECT id FROM roles WHERE nom=?");
        $st->execute([$name]);
        $id = $st->fetchColumn();
        return $id ? (int)$id : null;
    }

    public function dashboard(): void {
    $this->requireAdmin();
    $userId = (int)$_SESSION['user_id'];

    //données pour les graphiques.
    $trajetsData = $this->pdo->query("
        SELECT DATE(date_depart) AS jour, COUNT(*) AS total
        FROM trajets
        GROUP BY jour
        ORDER BY jour ASC
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    $creditsData = $this->pdo->query("
        SELECT DATE(date_reservation) AS jour, COUNT(*)*2 AS credits
        FROM reservations
        GROUP BY jour
        ORDER BY jour ASC
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    $platformCredits = (int)($this->pdo
        ->query("SELECT credits FROM users WHERE id = 1")
        ->fetchColumn() ?: 0);

    // liste des utilisateurs pour la  suspension 
    $st = $this->pdo->prepare("
        SELECT id, pseudo, email, credits, is_active
        FROM users
        WHERE id <> ?
          AND pseudo <> 'Admin'
        ORDER BY created_at DESC
    ");
    $st->execute([$userId]);
    $users = $st->fetchAll(PDO::FETCH_ASSOC);

    include ROOT . '/app/Views/admin/dashboard.php';
}

public function suspend(): void {
        $this->requireAdmin();
        $id = (int)($_POST['id'] ?? 0);

        if (!$id) { $_SESSION['flash_admin'] = "Utilisateur introuvable."; header('Location:/admin'); exit; }
        if ($id === (int)$_SESSION['user_id']) {
            $_SESSION['flash_admin'] = "Vous ne pouvez pas vous suspendre vous-même.";
            header('Location:/admin'); exit;
        }

        $st = $this->pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $st->execute([$id]);

        $_SESSION['flash_admin'] = "Utilisateur suspendu.";
        header('Location:/admin'); exit;
    }

    public function restore(): void {
        $this->requireAdmin();
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { $_SESSION['flash_admin'] = "Utilisateur introuvable."; header('Location:/admin'); exit; }

        $st = $this->pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
        $st->execute([$id]);

        $_SESSION['flash_admin'] = "Utilisateur réactivé.";
        header('Location:/admin'); exit;
    }

    /** POST /admin/employes — créer un employé (rôle employe) */
    public function createEmployee(): void {
        $this->requireAdmin();

        $pseudo   = trim($_POST['pseudo'] ?? '');
        $email    = mb_strtolower(trim($_POST['email'] ?? ''));
        $pass     = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        // validation
        if ($pseudo === '' || $email === '' || $pass === '' || $confirm === '') {
            $_SESSION['flash_admin'] = "Tous les champs sont obligatoires.";
            header('Location:/admin'); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_admin'] = "Email invalide.";
            header('Location:/admin'); exit;
        }
        if ($pass !== $confirm) {
            $_SESSION['flash_admin'] = "Les mots de passe ne correspondent pas.";
            header('Location:/admin'); exit;
        }
        // unicité de l'e-mail
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE email=?");
        $st->execute([$email]);
        if ($st->fetchColumn() > 0) {
            $_SESSION['flash_admin'] = "Cet email est déjà utilisé.";
            header('Location:/admin'); exit;
        }

        // créer un utilisateur
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $st = $this->pdo->prepare("
          INSERT INTO users (pseudo,email,password,created_at,photo,credits,is_active)
          VALUES (?,?,?, NOW(), 'default.png', 0, 1)
        ");
        $st->execute([$pseudo, $email, $hash]);
        $userId = (int)$this->pdo->lastInsertId();

        // lier le rôle  employe  dynamiquement (recherche par nom, pas par ID)
        if ($roleId = $this->roleIdByName('employe')) {
            $st = $this->pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?,?)");
            $st->execute([$userId, $roleId]);
        } else {
            // si le rôle n'existe pas — on peut le créer
            $this->pdo->prepare("INSERT INTO roles (nom) VALUES ('employe')")->execute();
            $roleId = (int)$this->pdo->lastInsertId();
            $this->pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?,?)")->execute([$userId, $roleId]);
        }

        $_SESSION['flash_admin'] = "Employé créé avec succès.";
        header('Location:/admin'); exit;
    }
}

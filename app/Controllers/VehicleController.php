<?php
class VehicleController
{
    public function __construct(private PDO $pdo) {}

    /*helper: vérifier que l’utilisateur a le rôle conducteur */
    private function isConducteur(int $userId): bool {
        $st = $this->pdo->prepare("
            SELECT 1
            FROM user_roles ur
            JOIN roles r ON r.id = ur.role_id
            WHERE ur.user_id = ? AND r.nom = 'conducteur'
            LIMIT 1
        ");
        $st->execute([$userId]);
        return (bool)$st->fetchColumn();
    }

    /* GET /vehicules/creer */
    public function createForm(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $userId = (int)$_SESSION['user_id'];

        // n’autoriser que les conducteurs
        if (!$this->isConducteur($userId)) {
            $_SESSION['flash_vehicle'] = "Accès réservé au conducteur.";
            header('Location:/mon_espace'); exit;
        }

        // toutes les préférences disponibles
        $preferences = $this->pdo->query("SELECT id, nom FROM preferences ORDER BY nom")
                                 ->fetchAll(PDO::FETCH_ASSOC);

        $errors = [];
        $old = $_POST; // pour réafficher les valeurs en cas d’erreurs
        include ROOT.'/app/Views/vehicules/create.php';
    }

    /** POST /vehicules/creer */
    public function store(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $userId = (int)$_SESSION['user_id'];

        if (!$this->isConducteur($userId)) {
        $_SESSION['flash_vehicle'] = "Accès réservé au conducteur.";
        header('Location:/mon_espace'); 
        exit;
        }

        $marque  = trim($_POST['marque']  ?? '');
        $modele  = trim($_POST['modele']  ?? '');
        $couleur = trim($_POST['couleur'] ?? '');
        $energie = trim($_POST['energie'] ?? '');
        $places  = (int)($_POST['places'] ?? 0);
        $prefs   = $_POST['preferences'] ?? [];

        $errors = [];
        if ($marque === '' || $modele === '' || $couleur === '' || $energie === '' || $places <= 0) {
            $errors[] = "Tous les champs du véhicule doivent être correctement remplis.";
        }
        if (!in_array($energie, ['essence','diesel','electrique'], true)) {
            $errors[] = "Type d'énergie invalide.";
        }

        if ($errors) {
            // réafficher le formulaire avec les erreurs
            $preferences = $this->pdo->query("SELECT id, nom FROM preferences ORDER BY nom")
                                     ->fetchAll(PDO::FETCH_ASSOC);
            $old = $_POST;
            include ROOT.'/app/Views/vehicules/create.php';
            return;
        }

        //INSERT vehicules 
        $st = $this->pdo->prepare("
            INSERT INTO vehicules (user_id, marque, modele, couleur, energie, places)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $st->execute([$userId, $marque, $modele, $couleur, $energie, $places]);
        $vehiculeId = (int)$this->pdo->lastInsertId();

        // association des préférences (si sélectionnées)
        if ($vehiculeId && is_array($prefs) && $prefs) {
            $ins = $this->pdo->prepare(
                "INSERT INTO vehicule_preferences (vehicule_id, preference_id) VALUES (?, ?)"
            );
            foreach ($prefs as $pid) {
                if (ctype_digit((string)$pid)) {
                    $ins->execute([$vehiculeId, (int)$pid]);
                }
            }
        }

        $_SESSION['flash_vehicle'] = "Véhicule ajouté.";
        header('Location:/mon_espace');
        exit;
    }
}

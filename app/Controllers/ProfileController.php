<?php
require_once __DIR__.'/../Models/User.php';
require_once __DIR__.'/../Models/Reservation.php';


class ProfileController {
    public function __construct(private PDO $pdo) {}

    // POST /profil/upload-photo
    public function uploadPhoto(): void {
        if (empty($_SESSION['user_id'])) { header('Location: /connexion'); exit; }

        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = "Erreur d'upload.";
            header('Location: /mon_espace'); exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $allowed = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            $_SESSION['flash'] = "Type de fichier non autorisé.";
            header('Location: /mon_espace'); exit;
        }

        if ($_FILES['photo']['size'] > 2*1024*1024) { // 2MB
            $_SESSION['flash'] = "Fichier trop volumineux (max 2 Mo).";
            header('Location: /mon_espace'); exit;
        }

        $dir = ROOT.'/public/assets/uploads';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $safeName = 'u'.$userId.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
        $target = $dir.'/'.$safeName;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $_SESSION['flash'] = "Impossible d'enregistrer le fichier.";
            header('Location: /mon_espace'); exit;
        }

        $u = new User($this->pdo);
        $u->setPhoto($userId, $safeName);

        $_SESSION['user']['photo'] = $safeName;
        $_SESSION['flash'] = "Photo mise à jour.";
        header('Location: /mon_espace'); exit;
    }
     /** GET /mon_espace */ public function dashboard(): void {
        if (empty($_SESSION['user_id'])) { header('Location: /connexion'); exit; }

        $uid = (int)$_SESSION['user_id'];

        // user
        $st = $this->pdo->prepare("SELECT * FROM users WHERE id=?");
        $st->execute([$uid]);
        $user = $st->fetch(PDO::FETCH_ASSOC);

        // roles
        $u = new User($this->pdo);
        $roles = $u->roles($uid);

        // vehicules
        $st = $this->pdo->prepare("SELECT * FROM vehicules WHERE user_id=?");
        $st->execute([$uid]);
        $vehicules = $st->fetchAll(PDO::FETCH_ASSOC);

        // credits
        $credits = (int)($user['credits'] ?? 0);

        // credits utilises
        $credits_utilises = null;
        if (in_array('passager', $roles, true)) {
            $st = $this->pdo->prepare("SELECT COUNT(*) FROM reservations WHERE passager_id=?");
            $st->execute([$uid]);
            $credits_utilises = (int)$st->fetchColumn();
        }

        // credits_gagnes (si conducteur)
        $credits_gagnes = null;
        if (in_array('conducteur', $roles, true)) {
            $st = $this->pdo->prepare("
                SELECT COUNT(*) 
                FROM reservations r
                JOIN trajets t ON r.trajet_id = t.id
                WHERE t.conducteur_id = ?
            ");
            $st->execute([$uid]);
            $credits_gagnes = (int)$st->fetchColumn();
        }
// conducteurs éligibles à être évalués (si l'utilisateur est passager)
$conducteursAvis = [];
if (in_array('passager', $roles, true)) {
    $conducteursAvis = (new Reservation($this->pdo))->driversForReview($uid);
}

        include ROOT.'/app/Views/profile/dashboard.php';
    }

  

    public function creditsForm(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $message = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
        include ROOT.'/app/Views/profile/credits.php';
    }

    public function creditsStore(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $montant = (int)($_POST['montant'] ?? 0);
        if ($montant>0) {
            $this->pdo->prepare("UPDATE users SET credits=credits+? WHERE id=?")->execute([$montant,$_SESSION['user_id']]);
            $_SESSION['flash']="✔ Votre compte a été crédité de $montant crédits!";
        } else {
            $_SESSION['flash']="Montant invalide.";
        }
        header('Location:/credits'); exit;
    }

    // POST /profil/vehicule
    public function vehicleStore(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $uid = (int)$_SESSION['user_id'];

        $marque  = trim($_POST['marque'] ?? '');
        $modele  = trim($_POST['modele'] ?? '');
        $couleur = trim($_POST['couleur'] ?? '');
        $energie = trim($_POST['energie'] ?? '');
        $places  = (int)($_POST['places'] ?? 0);
        $prefs   = (array)($_POST['preferences'] ?? []);

        if (!$marque || !$modele || !$couleur || !$energie || $places<=0) {
            $_SESSION['flash']="Tous les champs du véhicule doivent être correctement remplis.";
            header('Location:/mon_espace'); exit;
        }

        $this->pdo->prepare("INSERT INTO vehicules (user_id,marque,modele,couleur,energie,places) VALUES (?,?,?,?,?,?)")
            ->execute([$uid,$marque,$modele,$couleur,$energie,$places]);
        $vehId = (int)$this->pdo->lastInsertId();

        if ($prefs) {
            $ins = $this->pdo->prepare("INSERT INTO vehicule_preferences (vehicule_id, preference_id) VALUES (?,?)");
            foreach ($prefs as $p) if (ctype_digit((string)$p)) $ins->execute([$vehId,(int)$p]);
        }

        $_SESSION['flash']="Véhicule ajouté.";
        header('Location:/mon_espace'); exit;
    }

}

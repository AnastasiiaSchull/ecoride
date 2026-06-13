<?php
require_once __DIR__.'/../Models/Trajet.php';

class TrajetController {
    public function __construct(private PDO $pdo) {}

    // GET /trajets/details?id=123
    public function details(): void {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) { http_response_code(404); echo "Trajet non trouvé."; return; }

        $t = new Trajet($this->pdo);
        $trajet = $t->findDetails($id);
        if (!$trajet) { http_response_code(404); echo "Trajet non trouvé."; return; }

        $commentaires = [];
        if (!empty($trajet['commentaires'])) {
            $commentaires = array_filter(array_map('trim', explode('||', $trajet['commentaires'])));
        }

        include ROOT.'/app/Views/trajets/details.php';
    }
// GET /trajets/creer
    public function createForm(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }

        //vérifier que l’utilisateur possède le rôle  conducteur
        $st = $this->pdo->prepare("
            SELECT r.nom FROM user_roles ur JOIN roles r ON r.id=ur.role_id
            WHERE ur.user_id=?");
        $st->execute([$_SESSION['user_id']]);
        $roles = $st->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('conducteur', $roles, true)) { echo "Accès réservé aux conducteurs."; return; }

        // voitures de l’utilisateur
        $st = $this->pdo->prepare("SELECT * FROM vehicules WHERE user_id=?");
        $st->execute([$_SESSION['user_id']]);
        $vehicules = $st->fetchAll(PDO::FETCH_ASSOC);

        // prefs 
        $preferences = $this->pdo->query("SELECT * FROM preferences")->fetchAll(PDO::FETCH_ASSOC);

        include ROOT.'/app/Views/trajets/create.php';
    }

    // POST /trajets
    public function store(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $uid = (int)$_SESSION['user_id'];

        $ville_depart = trim($_POST['ville_depart'] ?? '');
        $ville_arrivee= trim($_POST['ville_arrivee'] ?? '');
        $date_depart  = $_POST['date_depart'] ?? '';
        $date_arrivee = $_POST['date_arrivee'] ?? '';
        $prix         = (int)($_POST['prix'] ?? 0);
        $vehicule_id  = (int)($_POST['vehicule_id'] ?? 0);

        $errors = [];
        if ($ville_depart==='' || $ville_arrivee==='') $errors[]="Villes requises.";
        if ($date_depart==='') $errors[]="Date départ requise.";
        if ($prix<=0) $errors[]="Prix invalide.";
        if ($vehicule_id<=0) $errors[]="Véhicule invalide.";

        if ($errors) { $_SESSION['flash']=implode(' ', $errors); header('Location:/trajets/creer'); exit; }

        // prendre les champs énergie et places du véhicule choisi
        $st = $this->pdo->prepare("SELECT energie, places FROM vehicules WHERE id=? AND user_id=?");
        $st->execute([$vehicule_id,$uid]);
        $v = $st->fetch(PDO::FETCH_ASSOC);
        if (!$v) { $_SESSION['flash']="Véhicule introuvable."; header('Location:/trajets/creer'); exit; }

        $eco = ($v['energie']==='electrique') ? 1 : 0;
        $places_dispo = (int)$v['places'];

        $ins = $this->pdo->prepare("
          INSERT INTO trajets (conducteur_id, vehicule_id, ville_depart, ville_arrivee, date_depart, date_arrivee, prix, places_dispo, eco, statut)
          VALUES (?,?,?,?,?,?,?,?,?,?)
        ");
        $ins->execute([$uid,$vehicule_id,$ville_depart,$ville_arrivee,$date_depart,$date_arrivee,$prix,$places_dispo,$eco,'à_venir']);

        header('Location:/mes_trajets'); exit;
    }

    // GET /mes_trajets
    public function mine(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $st = $this->pdo->prepare("SELECT * FROM trajets WHERE conducteur_id=? ORDER BY date_depart DESC");
        $st->execute([$_SESSION['user_id']]);
        $mes_trajets = $st->fetchAll(PDO::FETCH_ASSOC);

        include ROOT.'/app/Views/trajets/mes_trajets.php';
    }

    // POST /trajets/statut
    public function updateStatus(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $trajet_id = (int)($_POST['trajet_id'] ?? 0);
        $new       = $_POST['statut'] ?? '';

        if (!$trajet_id || !in_array($new,['en_cours','terminé'],true)) { http_response_code(400); echo "Statut invalide."; return; }

        // vérifier l’appartenance
        $st = $this->pdo->prepare("SELECT conducteur_id FROM trajets WHERE id=?");
        $st->execute([$trajet_id]);
        $t = $st->fetch(PDO::FETCH_ASSOC);
        if (!$t || (int)$t['conducteur_id'] !== (int)$_SESSION['user_id']) { http_response_code(403); echo "Action non autorisée."; return; }

        $upd = $this->pdo->prepare("UPDATE trajets SET statut=? WHERE id=?");
        $upd->execute([$new,$trajet_id]);

        //bonus - lors de la finalisation, créditer le compte pour chaque réservation confirmée
        if ($new==='terminé') {
            $st = $this->pdo->prepare("SELECT COUNT(*) FROM reservations WHERE trajet_id=? AND statut='confirmee'");
            $st->execute([$trajet_id]);
            $gain = (int)$st->fetchColumn();
            if ($gain>0) {
                $this->pdo->prepare("UPDATE users SET credits=credits+? WHERE id=?")->execute([$gain,$_SESSION['user_id']]);
            }
        }

        $_SESSION['flash']="Statut mis à jour.";
        header('Location:/mes_trajets'); exit;
    }

}

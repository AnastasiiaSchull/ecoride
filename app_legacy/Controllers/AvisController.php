<?php
require_once __DIR__.'/../Models/Avis.php';

class AvisController {
    public function __construct(private PDO $pdo) {}

    // GET /avis/nouveau?reservation_id=...
    public function createForm(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $uid = (int)$_SESSION['user_id'];
        $rid = (int)($_GET['reservation_id'] ?? 0);

        // vérifie que la réservation appartient à l'utilisateur + trajet passé
        $st = $this->pdo->prepare("
            SELECT r.id, t.conducteur_id, t.ville_depart, t.ville_arrivee, t.date_depart
            FROM reservations r
            JOIN trajets t ON t.id = r.trajet_id
            WHERE r.id=? AND r.passager_id=? AND r.statut <> 'annulee' AND t.date_depart < NOW()
        ");
        $st->execute([$rid, $uid]);
        $reservation = $st->fetch(PDO::FETCH_ASSOC);

        if (!$reservation) {
            $_SESSION['flash']="Réservation invalide ou trajet non terminé.";
            header('Location:/mes_reservations'); exit;
        }

        // pas de doublon d'avis pour ce duo passager/conducteur
        $st = $this->pdo->prepare("SELECT 1 FROM avis WHERE conducteur_id=? AND passager_id=?");
        $st->execute([(int)$reservation['conducteur_id'], $uid]);
        if ($st->fetchColumn()) {
            $_SESSION['flash']="Vous avez déjà laissé un avis pour ce conducteur.";
            header('Location:/mes_reservations'); exit;
        }

        include ROOT.'/app/Views/avis/create.php';
    }

       // POST /avis
    public function store(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $uid  = (int)$_SESSION['user_id'];
        $rid  = (int)($_POST['reservation_id'] ?? 0);
        $note = (int)($_POST['note'] ?? 0);
        $com  = trim($_POST['commentaire'] ?? '');

        // validation 
        if ($rid<=0 || $note<1 || $note>5 || $com==='') {
            $_SESSION['flash']="Champs invalides.";
            header('Location:/mes_reservations'); exit;
        }

        //récupère le conducteur depuis la BDD + revalide l'éligibilité
        $st = $this->pdo->prepare("
            SELECT t.conducteur_id
            FROM reservations r
            JOIN trajets t ON t.id = r.trajet_id
            WHERE r.id=? AND r.passager_id=? AND r.statut <> 'annulee' AND t.date_depart < NOW()
            LIMIT 1
        ");
        $st->execute([$rid, $uid]);
        $cid = (int)($st->fetchColumn() ?? 0);
        if ($cid <= 0) {
            $_SESSION['flash']="Réservation invalide ou trajet non terminé.";
            header('Location:/mes_reservations'); exit;
        }

        // empêche les doublons 
        $st = $this->pdo->prepare("SELECT 1 FROM avis WHERE conducteur_id=? AND passager_id=?");
        $st->execute([$cid, $uid]);
        if ($st->fetchColumn()) {
            $_SESSION['flash']="Avis déjà déposé pour ce conducteur.";
            header('Location:/mes_reservations'); exit;
        }

        $st = $this->pdo->prepare("
            INSERT INTO avis (conducteur_id, passager_id, note, commentaire, approuve, is_problem)
            VALUES (?, ?, ?, ?, NULL, 0)
        ");
        $st->execute([$cid, $uid, $note, $com]);

        $_SESSION['flash']="Merci, votre avis a été envoyé (en attente de modération).";
        header('Location:/mes_reservations'); exit;
    }

}

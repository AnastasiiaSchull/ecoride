<?php
require_once __DIR__ . '/../Models/Reservation.php';
require_once __DIR__ . '/../Models/Trajet.php';
require_once __DIR__ . '/../Models/User.php';

class ReservationController {
    public function __construct(private PDO $pdo) {}

    /** POST /reservations */
    public function create(): void {
        if (empty($_SESSION['user_id'])) { header('Location: /connexion'); exit; }

        $userId   = (int)$_SESSION['user_id'];
        $trajetId = (int)($_POST['trajet_id'] ?? 0);
        if (!$trajetId) { http_response_code(400); echo "ID du trajet manquant."; return; }

       
        $PLATFORM_USER_ID = (int)(getenv('PLATFORM_USER_ID') ?: 1);

        $users = new User($this->pdo);
        $traj  = new Trajet($this->pdo);
        $res   = new Reservation($this->pdo);

        try {
            $this->pdo->beginTransaction();

            // roles
            $roles = $users->roles($userId);
            if (!in_array('passager', $roles, true)) {
                throw new RuntimeException("Vous devez être passager pour participer.");
            }

            // verrouiller l’enregistrement du trajet
            $t = $traj->findForUpdate($trajetId);
            if (!$t || (int)$t['places_dispo'] <= 0) {
                throw new RuntimeException("Trajet introuvable ou plus de places.");
            }

            $price    = (int)$t['prix'];
            $driverId = (int)$t['conducteur_id'];

            if ($users->credits($userId) < $price) {
                throw new RuntimeException("Pas assez de crédits pour ce trajet.");
            }

            // créer la réservation (directement confirmée)
            $resId = $res->createConfirmed($trajetId, $userId);

            // decrement
            if (!$traj->decrementSeat($trajetId)) {
                throw new RuntimeException("Plus de places.");
            }

            // ajustements de crédits
            $users->adjustCredits($userId, -$price);
            $users->adjustCredits($driverId, max(0, $price - 2));
            $users->adjustCredits($PLATFORM_USER_ID, 2);

            $this->pdo->commit();

            $_SESSION['flash'] = "Réservation confirmée ! $price crédit" . ($price > 1 ? "s" : "") . " ont été débités.";
            header('Location: /confirmation'); exit;
        } catch (\Throwable $e) {
    $this->pdo->rollBack();
    $_SESSION['flash'] = $e->getMessage();
    header('Location: /trajets/details?id='.(int)$trajetId);
    exit;
}
    }

    /*GET /mes_reservations */
    public function my(): void {
        if (!isset($_SESSION['user_id'])) { header('Location: /connexion'); exit; }
        $r = new Reservation($this->pdo);
        $reservations = (new Reservation($this->pdo))->withTripsByUser((int)$_SESSION['user_id']);
include ROOT.'/app/Views/reservations/mes_reservations.php';

    }

    public function confirmation(): void {
    include ROOT.'/app/Views/reservations/confirmation.php';
}

}

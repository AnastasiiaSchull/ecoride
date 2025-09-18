<?php
class EmployeController {
    public function __construct(private PDO $pdo) {}

    private function requireEmploye(): void {
        if (empty($_SESSION['user_id'])) { header('Location:/connexion'); exit; }
        $st = $this->pdo->prepare("
            SELECT 1 FROM user_roles ur
            JOIN roles r ON r.id=ur.role_id
            WHERE ur.user_id=? AND r.nom='employe' LIMIT 1
        ");
        $st->execute([(int)$_SESSION['user_id']]);
        if (!$st->fetchColumn()) { http_response_code(403); echo 'Accès refusé.'; exit; }
    }

    /** GET /employe */
    public function dashboard(): void {
        $this->requireEmploye();

        // avis à modérer (approuve = 0)
        $avis = $this->pdo->query("
            SELECT a.id, a.commentaire, a.note,
                   u1.pseudo AS passager, u2.pseudo AS chauffeur
            FROM avis a
            JOIN users u1 ON a.passager_id = u1.id
            JOIN users u2 ON a.conducteur_id = u2.id
            WHERE a.approuve = 0
            ORDER BY a.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // voyages signalés (approuve=1 и is_problem=1)
        $problemes = $this->pdo->query("
            SELECT 
                a.id   AS avis_id,
                p.pseudo AS passager, p.email AS email_passager,
                c.pseudo AS chauffeur, c.email AS email_chauffeur,
                t.ville_depart, t.ville_arrivee, t.date_depart,
                a.commentaire AS description
            FROM avis a
            JOIN trajets t ON a.conducteur_id = t.conducteur_id
            JOIN users p ON a.passager_id = p.id
            JOIN users c ON a.conducteur_id = c.id
            WHERE a.approuve = 1 AND a.is_problem = 1
            ORDER BY a.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        include ROOT.'/app/Views/employe/dashboard.php';
    }

    /** POST /employe/moderation */
    public function moderate(): void {
        $this->requireEmploye();

        $avisId    = (int)($_POST['avis_id'] ?? 0);
        $action    = $_POST['action'] ?? '';
        $isProblem = isset($_POST['is_problem']) ? 1 : 0;

        if (!$avisId || !in_array($action, ['valider','refuser'], true)) {
            $_SESSION['flash'] = "Requête invalide.";
            header('Location:/employe'); exit;
        }

        $approuve = $action === 'valider' ? 1 : 0;

        $st = $this->pdo->prepare("UPDATE avis SET approuve=?, is_problem=? WHERE id=?");
        $st->execute([$approuve, $isProblem, $avisId]);

        $_SESSION['flash'] = "Avis mis à jour.";
        header('Location:/employe'); exit;
    }
}

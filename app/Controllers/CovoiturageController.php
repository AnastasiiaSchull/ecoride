<?php
require_once __DIR__ . '/../Models/Trajet.php';
require_once __DIR__ . '/../Models/Vehicle.php';

class CovoiturageController {
    public function __construct(private PDO $pdo) {}

    /** GET /covoiturages */
    public function search(): void {
        $t = new Trajet($this->pdo);

        $villesDepart  = $t->distinctDeparts();
        $villesArrivee = $t->distinctArrivees();
        $first         = $t->firstRoute();

        $depart      = $_GET['depart']      ?? ($first['ville_depart']  ?? null);
        $destination = $_GET['destination'] ?? ($first['ville_arrivee'] ?? null);
        $date        = $_GET['date']        ?? date('Y-m-d'); 
        $passager    = isset($_GET['passager']) ? (int)$_GET['passager'] : 1;
        $type        = $_GET['type']        ?? '';
        $filtre      = $_GET['filtre']      ?? 'ecologique';

        $trajets     = $t->searchAdvanced($depart, $destination, $date, $passager, $filtre);
        $trajetTitre = "{$depart} → {$destination}";

        // passer les variables à la vue
        include ROOT . '/app/Views/covoiturage/recherche.php';
    }

    //POST /trajets/creer 
    public function store(): void {
        if (!isset($_SESSION['user_id'])) { header("Location: /connexion"); exit; }
        $user_id = (int)$_SESSION['user_id'];

        $ville_depart  = trim($_POST['ville_depart']  ?? '');
        $ville_arrivee = trim($_POST['ville_arrivee'] ?? '');
        $date_depart   = $_POST['date_depart']   ?? '';
        $date_arrivee  = $_POST['date_arrivee']  ?? '';
        $prix          = $_POST['prix']          ?? '';
        $vehicule_id   = $_POST['vehicule_id']   ?? '';

        $errors = [];
        if ($ville_depart === '' || $ville_arrivee === '') $errors[] = "Les villes ne peuvent pas être vides.";
        if ($date_depart === '')                             $errors[] = "La date de départ est requise.";
        if (!is_numeric($prix) || $prix <= 0)               $errors[] = "Le prix doit être un nombre positif.";
        if (!is_numeric($vehicule_id))                      $errors[] = "Véhicule invalide.";

        if ($errors) {

            echo "Erreur :<br>" . implode("<br>", array_map('htmlspecialchars', $errors));
            exit;
        }

        $vehM = new Vehicle($this->pdo);
        $veh  = $vehM->find((int)$vehicule_id);
        if (!$veh) { echo "Véhicule introuvable."; exit; }

        $eco          = ($veh['energie'] === 'electrique') ? 1 : 0;
        $places_dispo = (int)$veh['places'];

        $t = new Trajet($this->pdo);
        $trajet_id = $t->create(
            $user_id,
            (int)$vehicule_id,
            $ville_depart,
            $ville_arrivee,
            $date_depart,
            $date_arrivee,
            (float)$prix,
            $places_dispo,
            $eco,
            'à_venir'
        );

        header("Location: /mes_trajets");
        exit;
    }
}

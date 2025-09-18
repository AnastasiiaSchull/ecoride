<?php
require_once __DIR__.'/../Models/Trajet.php';

class ApiController {
    public function __construct(private PDO $pdo) {}

    // GET /api/trajets/dates?type=depart&ville=X&other=Y
    public function dates(): void {
        header('Content-Type: application/json; charset=utf-8');
        $type  = $_GET['type']  ?? null;
        $ville = $_GET['ville'] ?? null;
        $other = $_GET['other'] ?? null;
        if (!$type || !$ville || !$other) { http_response_code(400); echo json_encode(['error'=>'Paramètre manquant']); return; }
        $t = new Trajet($this->pdo);
        echo json_encode($t->datesBetween($ville, $other, $type));
    }

    // GET /api/trajets/departs?destination=Paris
    public function departs(): void {
        header('Content-Type: application/json; charset=utf-8');
        $destination = $_GET['destination'] ?? null;
        if (!$destination) { http_response_code(400); echo json_encode(['error'=>'Paramètre manquant']); return; }
        $t = new Trajet($this->pdo);
        echo json_encode($t->departsForDestination($destination));
    }

    // GET /api/trajets/destinations?depart=Lyon
    public function destinations(): void {
        header('Content-Type: application/json; charset=utf-8');
        $depart = $_GET['depart'] ?? null;
        if (!$depart) { http_response_code(400); echo json_encode(['error'=>'Paramètre manquant']); return; }
        $t = new Trajet($this->pdo);
        echo json_encode($t->destinationsForDepart($depart));
    }

    // GET /api/trajets/places?ville_depart=Lyon&ville_arrivee=Paris&date=2025-09-01
    public function places(): void {
        header('Content-Type: application/json; charset=utf-8');
        $vd = $_GET['ville_depart']  ?? null;
        $va = $_GET['ville_arrivee'] ?? null;
        $d  = $_GET['date']          ?? null;
        if (!$vd || !$va || !$d) { http_response_code(400); echo json_encode(['error'=>'Paramètre manquant']); return; }
        $t = new Trajet($this->pdo);
        echo json_encode(['places_max' => $t->maxPlacesFor($vd,$va,$d)]);
    }
}

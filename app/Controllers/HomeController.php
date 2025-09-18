<?php
require_once __DIR__ . '/../Models/Trajet.php';

class HomeController {
    public function __construct(private PDO $pdo) {}

    /** GET /  */
    public function index(): void {
        $t = new Trajet($this->pdo);

        $villesDepart  = $t->distinctDeparts();
        $villesArrivee = $t->distinctArrivees();
        $trajetsAVenir = $t->upcomingShort(3); 

        include ROOT . '/app/Views/home/index.php';
    }
}

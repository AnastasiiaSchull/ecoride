<?php

namespace App\Controller;

use App\Repository\TrajetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private TrajetRepository $trajetRepository
    ) {}

    // =========================
    // GET /
    // =========================
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        $villesDepart = $this->trajetRepository->findDistinctDepartures();
        $villesArrivee = $this->trajetRepository->findDistinctArrivals();

        $trajetsAVenir = $this->trajetRepository->find(3);

        return $this->render('covoiturage/recherche.html.twig', [
            'villesDepart'  => $villesDepart,
            'villesArrivee' => $villesArrivee,
            'trajetsAVenir' => $trajetsAVenir,
        ]);
    }
 
}

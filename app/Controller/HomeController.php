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
        $villesDepart = $this->trajetRepository->findDistinctDepartCities();
        $villesArrivee = $this->trajetRepository->findDistinctArrivalCities();

        $trajetsAVenir = $this->trajetRepository->findUpcomingLimited(3);

        return $this->render('home/index.html.twig', [
            'villesDepart'  => $villesDepart,
            'villesArrivee' => $villesArrivee,
            'trajetsAVenir' => $trajetsAVenir,
        ]);
    }
}
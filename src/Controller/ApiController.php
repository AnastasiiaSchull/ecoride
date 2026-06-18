<?php

namespace App\Controller;

use App\Repository\TrajetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    public function __construct(
        private TrajetRepository $trajetRepository
    ) {}

    #[Route('/api/trajets/dates', name: 'api_trajets_dates', methods: ['GET'])]
    public function dates(Request $request): JsonResponse
    {
        $type  = $request->query->get('type');
        $ville = $request->query->get('ville');
        $other = $request->query->get('other');

        if (!$type || !$ville || !$other) {
            return $this->json(['error' => 'Paramètre manquant'], 400);
        }

        $data = $this->trajetRepository->getDatesBetween($ville, $other, $type);

        return $this->json($data);
    }

    #[Route('/api/trajets/departs', name: 'api_trajets_departs', methods: ['GET'])]
    public function departs(Request $request): JsonResponse
    {
        $destination = $request->query->get('destination');

        if (!$destination) {
            return $this->json(['error' => 'Paramètre manquant'], 400);
        }

        $data = $this->trajetRepository->getDepartsForDestination($destination);

        return $this->json($data);
    }

    #[Route('/api/trajets/destinations', name: 'api_trajets_destinations', methods: ['GET'])]
    public function destinations(Request $request): JsonResponse
    {
        $depart = $request->query->get('depart');

        if (!$depart) {
            return $this->json(['error' => 'Paramètre manquant'], 400);
        }

        $data = $this->trajetRepository->getDestinationsForDepart($depart);

        return $this->json($data);
    }

    #[Route('/api/trajets/places', name: 'api_trajets_places', methods: ['GET'])]
    public function places(Request $request): JsonResponse
    {
        $vd = $request->query->get('ville_depart');
        $va = $request->query->get('ville_arrivee');
        $d  = $request->query->get('date');

        if (!$vd || !$va || !$d) {
            return $this->json(['error' => 'Paramètre manquant'], 400);
        }

        $places = $this->trajetRepository->getMaxPlacesFor($vd, $va, $d);

        return $this->json([
            'places_max' => $places
        ]);
    }
    #[Route('/api/trajets/villes-depart', name: 'api_trajets_villes_depart')]
        public function villesDepart(): JsonResponse
        {
            return $this->json(
                $this->trajetRepository->findDistinctDepartures()
            );
        }
}
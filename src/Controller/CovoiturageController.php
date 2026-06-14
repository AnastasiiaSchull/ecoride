<?php

namespace App\Controller;

use App\Entity\Trajet;
use App\Repository\TrajetRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CovoiturageController extends AbstractController
{
    public function __construct(
        private TrajetRepository $trajetRepository,
        private VehiculeRepository $vehiculeRepository,
        private EntityManagerInterface $em
    ) {}

    // =========================
    // GET /covoiturages
    // =========================
    #[Route('/covoiturages', name: 'covoiturage_search', methods: ['GET'])]
    public function search(Request $request): Response
        {
            $villesDepart = $this->trajetRepository->findDistinctDepartures();
            $villesArrivee = $this->trajetRepository->findDistinctArrivals();

            $first = $this->trajetRepository->findOneBy([], ['id' => 'ASC']);

            $depart      = $request->query->get('depart');
            $destination = $request->query->get('destination');
            $dateString  = $request->query->get('date');
            $passager    = (int) $request->query->get('passager', 1);
            $filtre      = $request->query->get('filtre');

            // 🔥 conversion date propre
            $date = null;
            if (!empty($dateString)) {
                $date = new \DateTime($dateString);
            }

            // 🚨 évite afficher tout si rien sélectionné
            $trajets = [];

            if ($depart || $destination || $date || $passager > 1) {
                $trajets = $this->trajetRepository->searchTrajets(
                    $depart,
                    $destination,
                    $date,
                    $passager
                );
            }

            return $this->render('covoiturage/recherche.html.twig', [
                'villesDepart'  => $villesDepart,
                'villesArrivee' => $villesArrivee,
                'trajets'       => $trajets,
                'depart'        => $depart,
                'destination'   => $destination,
                'date'          => $dateString,
                'passager'      => $passager,
            ]);
        }

    // =========================
    // POST /trajets/creer
    // =========================
    #[Route('/trajets/creer', name: 'trajet_create', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $villeDepart  = trim($request->request->get('ville_depart', ''));
        $villeArrivee = trim($request->request->get('ville_arrivee', ''));
        $dateDepart   = $request->request->get('date_depart');
        $dateArrivee  = $request->request->get('date_arrivee');
        $prix         = (float) $request->request->get('prix');
        $vehiculeId   = (int) $request->request->get('vehicule_id');

        $errors = [];

        if ($villeDepart === '' || $villeArrivee === '') {
            $errors[] = 'Les villes ne peuvent pas être vides.';
        }

        if (!$dateDepart) {
            $errors[] = 'La date de départ est requise.';
        }

        if ($prix <= 0) {
            $errors[] = 'Le prix doit être positif.';
        }

        $vehicule = $this->vehiculeRepository->find($vehiculeId);
        if (!$vehicule) {
            $errors[] = 'Véhicule introuvable.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }

            return $this->redirectToRoute('trajet_form');
        }

        $trajet = new Trajet();
        $trajet->setConducteur($user);
        $trajet->setVehicule($vehicule);
        $trajet->setVilleDepart($villeDepart);
        $trajet->setVilleArrivee($villeArrivee);
        $trajet->setDateDepart(new \DateTime($dateDepart));

        if ($dateArrivee) {
            $trajet->setDateArrivee(new \DateTime($dateArrivee));
        }

        $trajet->setPrix($prix);
        $trajet->setPlacesDispo($vehicule->getPlaces());
        $trajet->setEco($vehicule->getEnergie() === 'electrique');
        $trajet->setStatut('a_venir');

        $this->em->persist($trajet);
        $this->em->flush();

        return $this->redirectToRoute('mes_trajets');
    }
}
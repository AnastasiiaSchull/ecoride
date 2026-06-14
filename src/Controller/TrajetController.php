<?php

namespace App\Controller;

use App\Repository\TrajetRepository;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TrajetController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private TrajetRepository $trajetRepository,
        private VehiculeRepository $vehiculeRepository,
        private ReservationRepository $reservationRepository
    ) {}

     #[Route('/recherche', name: 'trajet_search', methods: ['GET'])]
        public function search(Request $request): Response
        {
            $depart = $request->query->get('depart');
            $arrivee = $request->query->get('destination');
            $date = $request->query->get('date');
            $places = $request->query->get('passager');

            $trajets = [];

            if ($depart || $arrivee || $date || $places) {
                $trajets = $this->trajetRepository->searchTrajets(
                    $depart,
                    $arrivee,
                    $date ? new \DateTime($date) : null,
                    $places ? (int) $places : null
                );
            }

            return $this->render('trajets/recherche.html.twig', [
                'trajets' => $trajets,
                'depart' => $depart,
                'destination' => $arrivee,
                'date' => $date,
                'passager' => $places,
            ]);
        }
    // =========================
    // DETAILS
    // =========================
    #[Route('/trajets/details', name: 'trajet_details', methods: ['GET'])]
    public function details(Request $request): Response
    {
        $id = (int) $request->query->get('id', 0);

        if ($id <= 0) {
            throw $this->createNotFoundException('Trajet non trouvé');
        }

        $trajet = $this->trajetRepository->find($id);

        if (!$trajet) {
            throw $this->createNotFoundException('Trajet non trouvé');
        }

        $commentaires = [];

        if ($trajet->getCommentaires()) {
            $commentaires = array_filter(
                array_map('trim', explode('||', $trajet->getCommentaires()))
            );
        }

        return $this->render('trajets/details.html.twig', [
            'trajet' => $trajet,
            'commentaires' => $commentaires,
        ]);
    }

    // =========================
    // CREATE FORM
    // =========================
    #[Route('/trajets/creer', name: 'trajet_create_form', methods: ['GET'])]
    public function new(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');

        $user = $this->getUser();

        $vehicules = $this->vehiculeRepository->findBy([
            'user' => $user
        ]);

        $preferences = $this->em
            ->getConnection()
            ->fetchAllAssociative('SELECT * FROM preferences');

        return $this->render('trajets/create.html.twig', [
            'vehicules' => $vehicules,
            'preferences' => $preferences
        ]);
    }

    // =========================
    // STORE TRAJET
    // =========================
    #[Route('/trajets', name: 'trajet_store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');

        $user = $this->getUser();

        $villeDepart = trim($request->request->get('ville_depart'));
        $villeArrivee = trim($request->request->get('ville_arrivee'));
        $dateDepart = $request->request->get('date_depart');
        $dateArrivee = $request->request->get('date_arrivee');
        $prix = (int) $request->request->get('prix');
        $vehiculeId = (int) $request->request->get('vehicule_id');

        if (!$villeDepart || !$villeArrivee || !$dateDepart || $prix <= 0 || $vehiculeId <= 0) {
            $this->addFlash('error', 'Champs invalides.');
            return $this->redirectToRoute('trajet_create_form');
        }

        $vehicule = $this->vehiculeRepository->find($vehiculeId);

        if (!$vehicule || $vehicule->getUser() !== $user) {
            $this->addFlash('error', 'Véhicule invalide.');
            return $this->redirectToRoute('trajet_create_form');
        }

        $trajet = new \App\Entity\Trajet();
        $trajet->setConducteur($user);
        $trajet->setVehicule($vehicule);
        $trajet->setVilleDepart($villeDepart);
        $trajet->setVilleArrivee($villeArrivee);
        $trajet->setDateDepart(new \DateTimeImmutable($dateDepart));
        $trajet->setDateArrivee(new \DateTimeImmutable($dateArrivee));
        $trajet->setPrix($prix);
        $trajet->setPlacesDispo($vehicule->getPlaces());
        $trajet->setEco($vehicule->getEnergie() === 'electrique');
        $trajet->setStatut('à_venir');

        $this->em->persist($trajet);
        $this->em->flush();

        return $this->redirectToRoute('trajet_mine');
    }

    // =========================
    // MES TRAJETS
    // =========================
    #[Route('/mes_trajets', name: 'trajet_mine', methods: ['GET'])]
    public function mine(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');

        $trajets = $this->trajetRepository->findBy(
            ['conducteur' => $this->getUser()],
            ['dateDepart' => 'DESC']
        );

        return $this->render('trajets/mes_trajets.html.twig', [
            'trajets' => $trajets
        ]);
    }

    // =========================
    // UPDATE STATUS
    // =========================
    #[Route('/trajets/statut', name: 'trajet_status', methods: ['POST'])]
    public function updateStatus(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');
        
        $trajetId = (int) $request->request->get('trajet_id');
        $new = $request->request->get('statut');

        $trajet = $this->trajetRepository->find($trajetId);

        if (!$trajet || $trajet->getConducteur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if (!in_array($new, ['en_cours', 'terminé'], true)) {
            $this->addFlash('error', 'Statut invalide');
            return $this->redirectToRoute('trajet_mine');
        }

        $trajet->setStatut($new);

        // bonus crédits
        if ($new === 'terminé') {
            $gain = $this->reservationRepository->countConfirmedByTrajet($trajetId);

            if ($gain > 0) {
                $user = $this->getUser();
                if (!$user instanceof \App\Entity\User) {
                    throw new \LogicException('User attendu');
                }
                $user->addCredits($gain);
            }
        }

        $this->em->flush();

        $this->addFlash('success', 'Statut mis à jour.');
        return $this->redirectToRoute('trajet_mine');
    }
}
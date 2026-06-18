<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Trajet;
use App\Form\TrajetFormType;
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
    public function details(): Response
    {
       $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');

    $conducteur = $this->getUser();

    $trajetsConducteur = $this->trajetRepository->findBy([
        'conducteur' => $conducteur
    ]);

    return $this->render('trajets/ses_trajets.html.twig', [
        'trajets' => $trajetsConducteur
    ]);
    }

    // =========================
    // CREATE FORM
    // =========================
    #[Route('/trajets/creer', name: 'trajet_create_form')]
        public function new(Request $request, EntityManagerInterface $em): Response
        {
            //dd($request->request->all());
            $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');
            
            $trajet = new Trajet();

            $form = $this->createForm(TrajetFormType::class, $trajet);
            $form->handleRequest($request);

            if ($form->isSubmitted() && !$form->isValid()) {
                dump((string) $form->getErrors(true, false));
            }

            if ($form->isSubmitted() && $form->isValid()) {

                $trajet->setConducteur($this->getUser());

                $em->persist($trajet);
                $em->flush();

                return $this->redirectToRoute('trajet_details');
            }
            
            return $this->render('trajets/create.html.twig', [
                'form' => $form->createView()
            ]);
        }

    // =========================
    // STORE TRAJET
    // =========================
    
    #[Route('/trajets', name: 'trajet_store', methods: ['POST'])]
        public function store(Request $request): Response
        {
            $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');

            /** @var User $user */
            $user = $this->getUser();

            // 🔥 IMPORTANT : récupérer le bon namespace Symfony Form
            $trajetData = $request->request->all()['trajet_form'] ?? [];

            $villeDepart = trim($trajetData['villeDepart'] ?? '');
            $villeArrivee = trim($trajetData['villeArrivee'] ?? '');
            $dateDepart = $trajetData['dateDepart'] ?? null;
            $dateArrivee = $trajetData['dateArrivee'] ?? null;
            $prix = (float) ($trajetData['prix'] ?? 0);
            $vehicule = $trajetData['vehicule'] ?? null;

            if (is_object($vehicule)) {
            $vehiculeId = $vehicule->getId();
            }

            //$vehiculeId = (int) $vehiculeId;

            if (!$villeDepart || !$villeArrivee || !$dateDepart || $prix <= 0 || $vehicule <= 0) {
                $this->addFlash('error', 'Champs invalides.');
                return $this->redirectToRoute('trajet_create_form');
            }
           
            $vehicule = $this->vehiculeRepository->find((int)$vehicule);

            $trajet = new \App\Entity\Trajet();

            $trajet->setConducteur($user);
            $trajet->setVehicule($vehicule);
            $trajet->setVilleDepart($villeDepart);
            $trajet->setVilleArrivee($villeArrivee);
            $trajet->setDateDepart(new \DateTime($dateDepart));
            $trajet->setDateArrivee(new \DateTime($dateArrivee));
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
        public function mine(TrajetRepository $trajetRepository): Response
        {
            $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');

            $user = $this->getUser();

            $trajets = $trajetRepository->findBy(
                ['conducteur' => $user],
                ['dateDepart' => 'DESC']
            );

            return $this->render('trajets/details.html.twig', [
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
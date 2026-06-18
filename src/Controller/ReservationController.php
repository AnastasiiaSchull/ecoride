<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\TrajetRepository;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReservationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private TrajetRepository $trajetRepository,
        private UserRepository $userRepository,
        private ReservationRepository $reservationRepository
    ) {}

    // =========================
    // CREATE RESERVATION
    // =========================
    #[Route('/reservations', name: 'reservation_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User attendu');
        }
        
        $trajetId = (int) $request->request->get('trajet_id');

        if (!$trajetId) {
            $this->addFlash('error', 'ID du trajet manquant.');
            return $this->redirectToRoute('profile_dashboard');
        }

        $trajet = $this->trajetRepository->find($trajetId);

        if (!$trajet || $trajet->getPlacesDispo() <= 0) {
            $this->addFlash('error', 'Trajet introuvable ou complet.');
            return $this->redirectToRoute('profile_dashboard');
        }

        if (!in_array('ROLE_PASSAGER', $user->getRoles(), true)) {
            $this->addFlash('error', 'Vous devez être passager pour réserver.');
            return $this->redirectToRoute('profile_dashboard');
        }

        $price = (int) $trajet->getPrix();
        $driver = $trajet->getConducteur();

        if ($user->getCredits() < $price) {
            $this->addFlash('error', 'Crédits insuffisants.');
            return $this->redirectToRoute('home');
        }

        try {
            $this->em->beginTransaction();

            // réservation
            $reservation = new Reservation();
            $reservation->setTrajet($trajet);
            $reservation->setPassager($user);
            $reservation->setStatut(Reservation::CONFIRMEE);

            // places
            $trajet->setPlacesDispo($trajet->getPlacesDispo() - 1);

            // crédits
            $user->setCredits($user->getCredits() - $price);
            $driver->setCredits($driver->getCredits() + max(0, $price - 2));

            $this->em->persist($reservation);

            $this->em->flush();
            $this->em->commit();

            $this->addFlash('success', 'Réservation confirmée !');

            return $this->redirectToRoute('reservation_confirmation');

        } catch (\Throwable $e) {
            $this->em->rollback();

            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('home');
        }
    }

    // =========================
    // MY RESERVATIONS
    // =========================
    #[Route('/mes_reservations', name: 'reservation_my', methods: ['GET'])]
    public function my(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $reservations = $this->reservationRepository
            ->findBy(['passager' => $user]);
        if ($this->isGranted('ROLE_CONDUCTEUR') && $this->isGranted('ROLE_PASSAGER') ) {
        

        return $this->render('reservations/mes_reservations.html.twig', [
            'reservations' => $reservations
        ]);
        }
        if ($this->isGranted('ROLE_CONDUCTEUR')) {
        $this->addFlash('error', "Pas de réservation en tant que conducteur !");

        return $this->redirectToRoute('profile_dashboard');
        }

        

        return $this->render('reservations/mes_reservations.html.twig', [
            'reservations' => $reservations
        ]);
    }

    // =========================
    // CONFIRMATION PAGE
    // =========================
    #[Route('/confirmation', name: 'reservation_confirmation', methods: ['GET'])]
    public function confirmation(): Response
    {
        return $this->render('reservations/confirmation.html.twig');
    }
}
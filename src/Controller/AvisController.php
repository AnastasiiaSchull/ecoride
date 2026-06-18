<?php

namespace App\Controller;

use App\Repository\AvisRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AvisController extends AbstractController
{
    public function __construct(
        private ReservationRepository $reservationRepository,
        private AvisRepository $avisRepository
    ) {}

    #[Route('/avis/nouveau/{id}', name: 'avis_create_form', methods: ['GET','POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $reservationId = (int) $request->attributes->get('id');
        
        $reservation = $this->reservationRepository->createQueryBuilder('r')
            ->join('r.trajet', 't')
            ->where('r.id = :id')
            ->andWhere('r.passager = :user')
            ->andWhere('r.statut != :cancel')
            ->andWhere('t.dateDepart < :now')
            ->setParameter('id', $reservationId)
            ->setParameter('user', $user)
            ->setParameter('cancel', 'annulee')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();

        if (!$reservation) {
            $this->addFlash('error', 'Réservation invalide ou trajet non terminé.');
            return $this->redirectToRoute('reservation_my');
        }

        $existing = $this->avisRepository->findOneBy([
            'reservation' => $reservation
        ]);

        if ($existing) {
            $this->addFlash('error', 'Vous avez déjà laissé un avis pour cette réservation.');
            return $this->redirectToRoute('mes_reservations');
        }

        return $this->render('avis/create.html.twig', [
            'reservation' => $reservation
        ]);
    }

    
}
<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\ReservationRepository;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AvisController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReservationRepository $reservationRepository,
        private AvisRepository $avisRepository
    ) {}

    #[Route('/avis/nouveau', name: 'avis_create_form', methods: ['GET'])]
        public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        $reservationId = (int) $request->query->get('reservation_id');

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
            return $this->redirectToRoute('mes_reservations');
        }

        $existing = $this->avisRepository->findOneBy([
            'conducteur' => $reservation->getTrajet()->getConducteur(),
            'passager' => $user
        ]);

        if ($existing) {
            $this->addFlash('error', 'Vous avez déjà laissé un avis.');
            return $this->redirectToRoute('mes_reservations');
        }

        return $this->render('avis/create.html.twig', [
            'reservation' => $reservation
        ]);
    }

    #[Route('/avis', name: 'avis_store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $reservationId = (int) $request->request->get('reservation_id');
        $note = (int) $request->request->get('note');
        $commentaire = trim($request->request->get('commentaire'));

        if ($reservationId <= 0 || $note < 1 || $note > 5 || $commentaire === '') {
            $this->addFlash('error', 'Champs invalides.');
            return $this->redirectToRoute('mes_reservations');
        }

        $reservation = $this->reservationRepository->find($reservationId);

        if (!$reservation || $reservation->getPassager() !== $user) {
            $this->addFlash('error', 'Réservation invalide.');
            return $this->redirectToRoute('mes_reservations');
        }

        $trajet = $reservation->getTrajet();
        $conducteur = $trajet->getConducteur();

        // anti doublon
        $existing = $this->avisRepository->findOneBy([
            'conducteur' => $conducteur,
            'passager' => $user
        ]);

        if ($existing) {
            $this->addFlash('error', 'Avis déjà déposé.');
            return $this->redirectToRoute('mes_reservations');
        }

        $avis = new Avis();
        $avis->setConducteur($conducteur);
        $avis->setPassager($user);
        $avis->setNote($note);
        $avis->setCommentaire($commentaire);
        //$avis->setApprouve(null);
        //$avis->setIsProblem(false);

        $this->em->persist($avis);
        $this->em->flush();

        $this->addFlash('success', 'Merci, avis envoyé en modération.');
        return $this->redirectToRoute('mes_reservations');
    }
}
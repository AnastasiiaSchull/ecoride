<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Retourne les réservations d'un passager.
     */
    public function findByPassager(int $passagerId): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.trajet', 't')
            ->addSelect('t')
            ->where('r.passager = :passager')
            ->setParameter('passager', $passagerId)
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les réservations d'un trajet.
     */
    public function findByTrajet(int $trajetId): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.passager', 'p')
            ->addSelect('p')
            ->where('r.trajet = :trajet')
            ->setParameter('trajet', $trajetId)
            ->orderBy('r.dateReservation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre de réservations confirmées pour un trajet.
     */
    public function countConfirmedReservationsByTrajet(int $trajetId): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.trajet = :trajet')
            ->andWhere('r.statut = :statut')
            ->setParameter('trajet', $trajetId)
            ->setParameter('statut', Reservation::CONFIRMEE)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Vérifie si un utilisateur a déjà réservé un trajet.
     */
    public function hasReservation(int $trajetId, int $passagerId): bool
    {
        return (bool) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.trajet = :trajet')
            ->andWhere('r.passager = :passager')
            ->setParameter('trajet', $trajetId)
            ->setParameter('passager', $passagerId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne les réservations en attente.
     */
    public function findPendingReservations(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.statut = :statut')
            ->setParameter('statut', Reservation::EN_ATTENTE)
            ->orderBy('r.dateReservation', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
<?php

namespace App\Repository;

use App\Entity\Trajet;
use DateTimeInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TrajetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trajet::class);
    }

    /**
     * Retourne les trajets à venir.
     */
    public function findUpcomingTrajets(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.dateDepart >= :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('t.dateDepart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de trajets.
     */
    public function searchTrajets(
        ?string $depart = null,
        ?string $arrivee = null,
        ?\DateTimeInterface $date = null,
        ?int $places = null
    ): array {
        $qb = $this->createQueryBuilder('t');

        if ($depart) {
            $qb->andWhere('t.villeDepart = :depart')
                ->setParameter('depart', $depart);
        }

        if ($arrivee) {
            $qb->andWhere('t.villeArrivee = :arrivee')
                ->setParameter('arrivee', $arrivee);
        }

        if ($date) {

            if ($date instanceof \DateTimeImmutable) {
                $dateImmutable = $date;
            } elseif ($date instanceof \DateTimeInterface) {
                $dateImmutable = \DateTimeImmutable::createFromInterface($date);
            } else {
                $dateImmutable = new \DateTimeImmutable((string) $date);
            }

            $debutJour = $dateImmutable->setTime(0, 0, 0);
            $finJour = $dateImmutable->setTime(23, 59, 59);

            $qb->andWhere('t.dateDepart BETWEEN :debut AND :fin')
                ->setParameter('debut', $debutJour)
                ->setParameter('fin', $finJour);
        }

        if ($places) {
            $qb->andWhere('t.placesDispo >= :places')
                ->setParameter('places', $places);
        }

        return $qb
            ->andWhere('t.statut = :statut')
            ->setParameter('statut', 'a_venir')
            ->orderBy('t.dateDepart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trajets d'un conducteur.
     */
    public function findByConducteur(int $conducteurId): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.conducteur', 'c')
            ->andWhere('c.id = :id')
            ->setParameter('id', $conducteurId)
            ->orderBy('t.dateDepart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre de trajets créés par jour.
     * Utilisé pour Chart.js.
     */
    public function countTrajetsByDay(): array
    {
        $rows = $this->createQueryBuilder('t')
            ->select('DATE(t.dateDepart) AS jour')
            ->addSelect('COUNT(t.id) AS total')
            ->groupBy('jour')
            ->orderBy('jour', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $result = [];

        foreach ($rows as $row) {
            $result[$row['jour']] = (int) $row['total'];
        }

        return $result;
    }

    /**
     * Nombre total de trajets.
     */
    public function countAllTrajets(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trajets écologiques uniquement.
     */
    public function findEcoTrajets(): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.eco = :eco')
            ->setParameter('eco', true)
            ->orderBy('t.dateDepart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Derniers trajets créés.
     */
    public function findLastTrajets(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findDistinctDepartures(): array
    {
        return $this->createQueryBuilder('t')
            ->select('DISTINCT t.villeDepart')
            ->orderBy('t.villeDepart', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function findDistinctArrivals(): array
    {
        return $this->createQueryBuilder('t')
            ->select('DISTINCT t.villeArrivee')
            ->orderBy('t.villeArrivee', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
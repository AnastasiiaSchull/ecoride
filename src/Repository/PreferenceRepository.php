<?php

namespace App\Repository;

use App\Entity\Preference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PreferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Preference::class);
    }

    /**
     * Retourne toutes les préférences triées par nom.
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche une préférence par son nom.
     */
    public function findOneByNom(string $nom): ?Preference
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne les préférences les plus utilisées (par véhicules).
     */
    public function findMostUsed(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->select('p, COUNT(v.id) AS usageCount')
            ->join('p.vehicules', 'v')
            ->groupBy('p.id')
            ->orderBy('usageCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
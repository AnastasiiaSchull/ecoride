<?php

namespace App\Repository;

use App\Entity\Avis;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AvisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avis::class);
    }

    public function exists(User $conducteur, User $passager): bool
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.conducteur = :conducteur')
            ->andWhere('a.passager = :passager')
            ->setParameter('conducteur', $conducteur)
            ->setParameter('passager', $passager)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}
<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * Recherche un rôle par son nom.
     */
    public function findOneByNom(string $nom): ?Role
    {
        return $this->createQueryBuilder('r')
            ->where('r.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne tous les rôles triés par nom.
     */
    public function findAllOrderedByNom(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne le nombre d'utilisateurs ayant un rôle donné.
     */
    public function countUsersForRole(string $nom): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(u.id)')
            ->leftJoin('r.users', 'u')
            ->where('r.nom = :nom')
            ->setParameter('nom', $nom)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
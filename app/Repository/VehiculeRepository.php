<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Vehicule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class VehiculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicule::class);
    }

    /**
     * Premier véhicule d'un utilisateur.
     */
    public function findOneByUser(User $user): ?Vehicule
    {
        return $this->findOneBy([
            'user' => $user
        ]);
    }

    /**
     * Tous les véhicules d'un utilisateur.
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(
            ['user' => $user],
            ['id' => 'ASC']
        );
    }

    /**
     * Recherche par marque.
     */
    public function findByMarque(string $marque): array
    {
        return $this->findBy(
            ['marque' => $marque],
            ['modele' => 'ASC']
        );
    }

    /**
     * Recherche par énergie.
     */
    public function findByEnergie(string $energie): array
    {
        return $this->findBy(
            ['energie' => $energie],
            ['marque' => 'ASC']
        );
    }

    /**
     * Véhicules électriques.
     */
    public function findElectriques(): array
    {
        return $this->createQueryBuilder('v')
            ->where('LOWER(v.energie) = :energie')
            ->setParameter('energie', 'electrique')
            ->orderBy('v.marque', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre de véhicules d'un utilisateur.
     */
    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Véhicules ayant au moins X places.
     */
    public function findWithMinimumPlaces(int $places): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.places >= :places')
            ->setParameter('places', $places)
            ->orderBy('v.places', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche libre.
     */
    public function search(string $term): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.marque LIKE :term')
            ->orWhere('v.modele LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('v.marque', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

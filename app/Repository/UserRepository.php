<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Recherche un utilisateur par email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy([
            'email' => $email
        ]);
    }

    /**
     * Vérifie si un email existe déjà.
     */
    public function emailExists(string $email): bool
    {
        return $this->count([
            'email' => $email
        ]) > 0;
    }

    /**
     * Retourne le nombre de crédits.
     */
    public function getCredits(int $userId): int
    {
        $credits = $this->createQueryBuilder('u')
            ->select('u.credits')
            ->where('u.id = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        return $credits['credits'] ?? 0;
    }

    /**
     * Recherche par pseudo.
     */
    public function findByPseudo(string $pseudo): ?User
    {
        return $this->findOneBy([
            'pseudo' => $pseudo
        ]);
    }

    /**
     * Utilisateurs actifs.
     */
    public function findActiveUsers(): array
    {
        return $this->findBy(
            ['isActive' => true],
            ['pseudo' => 'ASC']
        );
    }

    /**
     * Utilisateurs inactifs.
     */
    public function findInactiveUsers(): array
    {
        return $this->findBy(
            ['isActive' => false],
            ['pseudo' => 'ASC']
        );
    }

    /**
     * Recherche partielle sur pseudo ou email.
     */
    public function search(string $term): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.pseudo LIKE :term')
            ->orWhere('u.email LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('u.pseudo', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
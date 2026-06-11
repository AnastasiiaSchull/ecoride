<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RoleController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    // =========================
    // EDIT PAGE
    // =========================
    #[Route('/roles/edit', name: 'roles_edit', methods: ['GET'])]
    public function edit(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $current = $user->getRoles();

        $all = [
            ['nom' => 'ROLE_CONDUCTEUR', 'label' => 'Conducteur'],
            ['nom' => 'ROLE_PASSAGER', 'label' => 'Passager'],
        ];

        return $this->render('roles/edit.html.twig', [
            'current' => $current,
            'all' => $all
        ]);
    }

    // =========================
    // UPDATE ROLES
    // =========================
    #[Route('/roles', name: 'roles_update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $allowed = ['ROLE_CONDUCTEUR', 'ROLE_PASSAGER'];

        $selected = $request->request->all('roles') ?? [];

        // sécurisation
        $selected = array_values(array_intersect($allowed, $selected));

        $roles = $user->getRoles();

        // retirer seulement les rôles modifiables
        $roles = array_filter($roles, function ($role) use ($allowed) {
            return !in_array($role, $allowed, true);
        });

        // ajouter sélection
        $roles = array_merge($roles, $selected);

        $user->setRoles(array_values(array_unique($roles)));

        // bonus logique métier (comme ton ancien code)
        if (in_array('ROLE_PASSAGER', $selected, true) && $user->getCredits() === 0) {
            $user->setCredits(5);
        }

        $this->em->flush();

        $this->addFlash('success', 'Rôles mis à jour.');

        return $this->redirectToRoute('profile_dashboard');
    }
}
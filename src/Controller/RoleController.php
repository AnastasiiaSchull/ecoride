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
        
    ) {}

    // =========================
    // EDIT PAGE
    // =========================
    #[Route('/roles/edit', name: 'roles_edit', methods: ['GET'])]
        public function edit(): Response
        {
            $this->denyAccessUnlessGranted('ROLE_USER');

            $user = $this->getUser();

            $roles = $user->getRoles();

            // ❌ on enlève ROLE_USER
            $current = array_filter($roles, function ($role) {
                return $role !== 'ROLE_USER';
            });

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
        public function update(Request $request, EntityManagerInterface $em): Response
        {
            $this->denyAccessUnlessGranted('ROLE_USER');

            /** @var User $user */
            $user = $this->getUser();

            $allowed = ['ROLE_CONDUCTEUR', 'ROLE_PASSAGER'];

            $selected = $request->request->all('roles') ?? [];

            // sécurité + 1 seul rôle métier max
            $selected = array_values(array_intersect($selected, $allowed));
            $selectedRole = $selected[0] ?? null;

            // on force base propre
            $roles = ['ROLE_USER'];

            // on ajoute UN SEUL rôle métier
            if ($selectedRole) {
                $roles[] = $selectedRole;
            }

            $user->setRoles($roles);

            // logique métier
            if ($selectedRole === 'ROLE_PASSAGER' && $user->getCredits() === 0) {
                $user->setCredits(5);
            }

            $em->flush();

            $this->addFlash('success', 'Rôle mis à jour.');

            return $this->redirectToRoute('profile_dashboard');
        }
}
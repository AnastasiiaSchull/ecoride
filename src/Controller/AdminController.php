<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private RoleRepository $roleRepository
    ) {}

    #[Route('/admin', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            !$this->isGranted('ROLE_EMPLOYE')
            ) {
                throw $this->createAccessDeniedException('Accès refusé.');
            }

        $conn = $this->em->getConnection();

        // Trajets par jour
        $trajetsData = $conn->fetchAllKeyValue("
            SELECT DATE(date_depart) AS jour, COUNT(*) AS total
            FROM trajet
            GROUP BY jour
            ORDER BY jour ASC
        ");

        // Crédits gagnés
        $creditsData = $conn->fetchAllKeyValue("
            SELECT DATE(date_reservation) AS jour, COUNT(*)*2 AS credits
            FROM reservation
            GROUP BY jour
            ORDER BY jour ASC
        ");

        // utilisateurs
        $users = $this->userRepository->findBy(
            [],
            ['id' => 'DESC']
        );

        return $this->render('admin/dashboard.html.twig', [
            'trajetsData' => $trajetsData,
            'creditsData' => $creditsData,
            'users' => $users,
        ]);
    }

    #[Route('/admin/suspend', name: 'admin_suspend', methods: ['POST'])]
    public function suspend(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = (int)$request->request->get('id');

        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Impossible de vous suspendre vous-même');
            return $this->redirectToRoute('admin_dashboard');
        }

        $user->setIsActive(false);

        $this->em->flush();

        $this->addFlash('success', 'Utilisateur suspendu');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/restore', name: 'admin_restore', methods: ['POST'])]
    public function restore(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $id = (int)$request->request->get('id');

        $user = $this->userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('admin_dashboard');
        }

        $user->setIsActive(true);
        $this->em->flush();

        $this->addFlash('success', 'Utilisateur réactivé');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/employes', name: 'admin_employes_create', methods: ['POST'])]
    public function createEmployee(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $pseudo = trim($request->request->get('pseudo'));
        $email = strtolower(trim($request->request->get('email')));
        $pass = $request->request->get('password');
        $confirm = $request->request->get('confirm_password');

        if (!$pseudo || !$email || !$pass || !$confirm) {
            $this->addFlash('error', 'Tous les champs sont obligatoires');
            return $this->redirectToRoute('admin_dashboard');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Email invalide');
            return $this->redirectToRoute('admin_dashboard');
        }

        if ($pass !== $confirm) {
            $this->addFlash('error', 'Mots de passe différents');
            return $this->redirectToRoute('admin_dashboard');
        }

        $existing = $this->userRepository->findOneBy(['email' => $email]);
        if ($existing) {
            $this->addFlash('error', 'Email déjà utilisé');
            return $this->redirectToRoute('admin_dashboard');
        }

        $user = new \App\Entity\User();
        $user->setPseudo($pseudo);
        $user->setEmail($email);
        $user->setPassword(password_hash($pass, PASSWORD_BCRYPT));
        $user->setIsActive(true);
        $user->setCredits(0);
        // rôle employé
        $user->addRole('ROLE_EMPLOYE');
        // rôle employé
        $role = $this->roleRepository->findOneBy(['nom' => 'employe']);
        if ($role) {
            $user->addRole('ROLE_EMPLOYE');
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('success', 'Employé créé');
        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/user/toggle', name: 'admin_user_toggle', methods: ['POST'])]
        public function toggle(Request $request): Response
        {
            if (
            !$this->isGranted('ROLE_ADMIN') &&
            !$this->isGranted('ROLE_EMPLOYE')
            ) {
                throw $this->createAccessDeniedException('Accès refusé.');
            }

            $id = (int) $request->request->get('id');
            $user = $this->userRepository->find($id);

            if (!$user) {
                $this->addFlash('error', 'Utilisateur introuvable');
                return $this->redirectToRoute('admin_dashboard');
            }

            if ($user === $this->getUser()) {
                $this->addFlash('error', 'Impossible de modifier votre propre statut');
                return $this->redirectToRoute('admin_dashboard');
            }

            // 🔁 TOGGLE ICI
            $user->setIsActive(!$user->isActive());

            $this->em->flush();

            $this->addFlash('success',
                $user->isActive()
                    ? 'Utilisateur réactivé'
                    : 'Utilisateur suspendu'
            );

            return $this->redirectToRoute('admin_dashboard');
        }
//==========MODERATION=========================================

        #[Route('/admin/avis', name: 'admin_avis')]
            public function avis(AvisRepository $avisRepository): Response
            {
                $avis = $avisRepository->findBy(
                    ['approuve' => false],
                    ['id' => 'DESC']
                );

                return $this->render('admin/avis.html.twig', [
                    'avis' => $avis
                ]);
            }

        #[Route('/admin/avis/{id}/valider', name: 'admin_avis_valider')]
            public function validerAvis(Avis $avis): Response
            {
                $avis->setApprouve(true);

                $this->em->flush();

                $this->addFlash('success', 'Avis validé.');

                return $this->redirectToRoute('admin_avis');
            }
}
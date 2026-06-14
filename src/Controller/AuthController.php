<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private RoleRepository $roleRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private Security $security
    ) {}

    #[Route('/connexion', name: 'login', methods: ['GET'])]
    public function loginForm(): Response
    {
        return $this->render('auth/login.html.twig');
    }

    #[Route('/connexion', name: 'login_post', methods: ['POST'])]
    public function login(Request $request): Response
    {
        $email = strtolower(trim($request->request->get('email', '')));
        $password = $request->request->get('password', '');

        if (!$email || !$password) {
            $this->addFlash('error', 'Email et mot de passe requis');
            return $this->redirectToRoute('login');
        }

        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $password)) {
            $this->addFlash('error', 'Identifiants invalides');
            return $this->redirectToRoute('login');
        }

        if (!$user->isActive()) {
            $this->addFlash('error', 'Compte suspendu');
            return $this->redirectToRoute('login');
        }

        // login manuel Symfony
        $this->security->login($user);

        return $this->redirectToRoute('home');
    }

    #[Route('/inscription', name: 'register', methods: ['GET'])]
    public function registerForm(): Response
    {
         return $this->render('auth/register.html.twig',[
            //'old' => $request->request->all(),
        ]);
    }

    #[Route('/inscription', name: 'register_post', methods: ['POST'])]
    public function register(Request $request): Response
    {
        $pseudo = trim($request->request->get('pseudo', ''));
        $email  = strtolower(trim($request->request->get('email', '')));
        $pass   = $request->request->get('password', '');
        $pass2  = $request->request->get('confirm_password', '');
        $roles  = $request->request->all('roles');

        if (!$pseudo || !$email || !$pass || !$pass2) {
            $this->addFlash('error', 'Tous les champs sont obligatoires');
            return $this->redirectToRoute('register');
        }

        if ($pass !== $pass2) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas');
            return $this->redirectToRoute('register');
        }

        if ($this->userRepository->findOneBy(['email' => $email])) {
            $this->addFlash('error', 'Email déjà utilisé');
            return $this->redirectToRoute('register');
        }

        $user = new User();
        $user->setPseudo($pseudo);
        $user->setEmail($email);
        $user->setIsActive(true);
        $user->setCredits(0);

        $hashed = $this->passwordHasher->hashPassword($user, $pass);
        $user->setPassword($hashed);

        // rôles (Symfony style)
        foreach ($roles as $roleName) {
            $role = $this->roleRepository->findOneBy(['nom' => $roleName]);
            if ($role) {
                $user->addRole($role->getRole());
            }
        }

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('success', 'Compte créé');
        return $this->redirectToRoute('login');
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        // Symfony gère automatiquement
        throw new \LogicException('This should never be reached.');
    }
}
<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private ReservationRepository $reservationRepository,
        private VehiculeRepository $vehiculeRepository
    ) {}

    // =========================
    // UPLOAD PHOTO
    // =========================
    #[Route('/profil/upload-photo', name: 'profile_upload_photo', methods: ['POST'])]
    public function uploadPhoto(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User attendu');
        }

        $file = $request->files->get('photo');

        if (!$file) {
            $this->addFlash('error', 'Erreur d\'upload.');
            return $this->redirectToRoute('profile_dashboard');
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower($file->guessExtension());

        if (!in_array($ext, $allowed, true)) {
            $this->addFlash('error', 'Type de fichier non autorisé.');
            return $this->redirectToRoute('profile_dashboard');
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            $this->addFlash('error', 'Fichier trop volumineux (max 2 Mo).');
            return $this->redirectToRoute('profile_dashboard');
        }

        $safeName = 'u' . $user->getId() . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        try {
            $file->move(
                $this->getParameter('uploads_directory'),
                $safeName
            );
        } catch (FileException $e) {
            $this->addFlash('error', 'Impossible d\'enregistrer le fichier.');
            return $this->redirectToRoute('profile_dashboard');
        }

        $user->setPhoto($safeName);
        $this->em->flush();

        $this->addFlash('success', 'Photo mise à jour.');
        return $this->redirectToRoute('profile_dashboard');
    }

    // =========================
    // DASHBOARD
    // =========================
    #[Route('/mon_espace', name: 'profile_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User attendu');
        }
        $uid = $user->getId();

        $roles = $user->getRoles();

        $vehicules = $this->vehiculeRepository->findBy(['user' => $user]);

        $credits = $user->getCredits();

        // crédits utilisés (passager)
        $creditsUtilises = null;
        if (in_array('ROLE_PASSAGER', $roles, true)) {
            $creditsUtilises = $this->reservationRepository->countByPassager($uid);
        }

        // crédits gagnés (conducteur)
        $creditsGagnes = null;
        if (in_array('ROLE_CONDUCTEUR', $roles, true)) {
            $creditsGagnes = $this->reservationRepository->countCreditsByDriver($uid);
        }

        // conducteurs à évaluer
        $conducteursAvis = [];
        if (in_array('ROLE_PASSAGER', $roles, true)) {
            $conducteursAvis = $this->reservationRepository->driversForReview($uid);
        }

        return $this->render('profile/dashboard.html.twig', [
            'user' => $user,
            'vehicules' => $vehicules,
            'credits' => $credits,
            'creditsUtilises' => $creditsUtilises,
            'creditsGagnes' => $creditsGagnes,
            'conducteursAvis' => $conducteursAvis,
        ]);
    }

    // =========================
    // CREDITS FORM
    // =========================
    #[Route('/credits', name: 'profile_credits_form', methods: ['GET'])]
    public function creditsForm(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('profile/credits.html.twig');
    }

    // =========================
    // CREDITS STORE
    // =========================
    #[Route('/credits', name: 'profile_credits_store', methods: ['POST'])]
    public function creditsStore(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $montant = (int) $request->request->get('montant', 0);

        $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User attendu');
        }

        if ($montant > 0) {
            $user->addCredits($montant);
            $this->em->flush();

            $this->addFlash('success', "✔ Crédit ajouté : $montant");
        } else {
            $this->addFlash('error', 'Montant invalide.');
        }

        return $this->redirectToRoute('profile_credits_form');
    }

    // =========================
    // VEHICULE STORE
    // =========================
    #[Route('/profil/vehicule', name: 'profile_vehicle_store', methods: ['POST'])]
    public function vehicleStore(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $marque  = trim($request->request->get('marque'));
        $modele  = trim($request->request->get('modele'));
        $couleur = trim($request->request->get('couleur'));
        $energie = trim($request->request->get('energie'));
        $places  = (int) $request->request->get('places');
        $prefs   = (array) $request->request->get('preferences', []);

        if (!$marque || !$modele || !$couleur || !$energie || $places <= 0) {
            $this->addFlash('error', 'Tous les champs sont obligatoires.');
            return $this->redirectToRoute('profile_dashboard');
        }

        $vehicule = new \App\Entity\Vehicule();
        $vehicule->setUser($user);
        $vehicule->setMarque($marque);
        $vehicule->setModele($modele);
        $vehicule->setCouleur($couleur);
        $vehicule->setEnergie($energie);
        $vehicule->setPlaces($places);

        $this->em->persist($vehicule);
        $this->em->flush();

        // TODO: gestion preferences via relation ManyToMany propre Doctrine

        $this->addFlash('success', 'Véhicule ajouté.');
        return $this->redirectToRoute('profile_dashboard');
    }
}
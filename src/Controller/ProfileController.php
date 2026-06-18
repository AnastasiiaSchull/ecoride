<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use App\Repository\TrajetRepository;
use App\Repository\PreferenceRepository;
use App\Repository\ReservationRepository;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ReservationRepository $reservationRepository,
        private VehiculeRepository $vehiculeRepository,
        private AvisRepository $avisRepository
    ) {}

    // =========================
    // UPLOAD PHOTO
    // =========================
    /*#[Route('/profil/upload-photo', name: 'profile_upload_photo', methods: ['POST'])]
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
            $targetDir = $this->getParameter('photos_directory');
            $file->move(
                $targetDir,
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
    }*/
    #[Route('/profil/upload-photo', name: 'profile_upload_photo', methods: ['POST'])]
        public function uploadPhoto(
            Request $request,
            FileUploader $uploader
        ): Response {

            $user = $this->getUser();
            if (!$user instanceof \App\Entity\User) {
                throw new \LogicException('User attendu');
            }
            

            $file = $request->files->get('photo');

            if (!$file) {
                $this->addFlash('error', 'Aucun fichier');
                return $this->redirectToRoute('profile_dashboard');
            }

            // 🔒 validation extension
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($file->guessExtension(), $allowed, true)) {
                $this->addFlash('error', 'Format invalide');
                return $this->redirectToRoute('profile_dashboard');
            }

            // 🔒 taille max 2MB
            if ($file->getSize() > 2 * 1024 * 1024) {
                $this->addFlash('error', 'Fichier trop lourd');
                return $this->redirectToRoute('profile_dashboard');
            }
            
            $targetDir = $this->getParameter('uploads_directory') . '/profils';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }
                
            $filename = $uploader->uploadProfilePicture(
                $file,
                $targetDir,
                'u' . $user->getId()
            );

            $user->setPhoto($filename);
            $this->em->flush();

            return $this->redirectToRoute('profile_dashboard');
        }
    // =========================
    // DASHBOARD
    // =========================
    #[Route('/mon_espace', name: 'profile_dashboard', methods: ['GET'])]
        public function dashboard(TrajetRepository $trajetRepository): Response
        {
            $this->denyAccessUnlessGranted('ROLE_USER');

            $user = $this->getUser();

            if (!$user instanceof \App\Entity\User) {
                throw new \LogicException('User attendu');
            }

            $uid = $user->getId();

            // ✅ FILTRAGE PROPRE DES ROLES
            $roles = array_values(array_filter($user->getRoles(), function ($role) {
                return $role !== 'ROLE_USER';
            }));

            $vehicules = $this->vehiculeRepository->findBy(['user' => $user]);

            $credits = $user->getCredits();

            // =========================
            // PASSAGER
            // =========================
            $creditsUtilises = null;

            if (in_array('ROLE_PASSAGER', $roles, true)) {
                $creditsUtilises = $this->reservationRepository->countByPassager($uid);
            }

            // =========================
            // CONDUCTEUR
            // =========================
            $creditsGagnes = null;

            if (in_array('ROLE_CONDUCTEUR', $roles, true)) {
                $creditsGagnes = $this->reservationRepository->countCreditsByDriver($uid);
            }

            // =========================
            // TRAJETS
            // =========================
            $trajets = $trajetRepository->findBy([
                'conducteur' => $this->getUser()
            ]);

        
            $conducteursAvis = [];

                if (in_array('ROLE_PASSAGER', $roles, true)) {

                    $conducteursAvis = $this->reservationRepository
                        ->createQueryBuilder('r')
                        ->join('r.trajet', 't')
                        ->leftJoin('App\Entity\Avis', 'a', 'WITH', 'a.reservation = r')
                        ->where('r.passager = :user')
                        ->andWhere('t.dateDepart < :now')
                        ->andWhere('r.statut != :cancel')
                        ->andWhere('a.id IS NULL')
                        ->setParameter('user', $user)
                        ->setParameter('now', new \DateTime())
                        ->setParameter('cancel', 'annulee')
                        ->getQuery()
                        ->getResult();
                }
            //dd($conducteursAvis);
            return $this->render('profile/dashboard.html.twig', [
                'user' => $user,
                'roles' => $roles,
                'trajets' => $trajets,
                'vehicules' => $vehicules,
                'credits' => $credits,
                'creditsUtilises' => $creditsUtilises,
                'creditsGagnes' => $creditsGagnes,
                'conducteursAvis' => $conducteursAvis
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
    #[Route('/profil/vehicule', name: 'profile_vehicule_store', methods: ['GET','POST'])]
        public function vehicleStore(Request $request, PreferenceRepository $preferenceRepository): Response
        {
            $this->denyAccessUnlessGranted('ROLE_USER');

            $user = $this->getUser();

            $marque  = trim($request->request->get('marque', ''));
            $modele  = trim($request->request->get('modele', ''));
            $couleur = trim($request->request->get('couleur', ''));
            $energie = trim($request->request->get('energie', ''));
            $places  = (int) $request->request->get('places', 0);

            $prefs = $request->request->all('preferences');
            //dd($prefs);
               

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

            // ✅ gestion des préférences propre
            foreach ($prefs as $prefName) {
                $pref = $preferenceRepository->findOneBy(['nom' => $prefName]);

                if ($pref) {
                    $vehicule->addPreference($pref);
                }
            }

            $this->em->persist($vehicule);
            $this->em->flush();

            $this->addFlash('success', 'Véhicule ajouté.');
            return $this->redirectToRoute('profile_dashboard');
        }
    // ======================================
    // AVIS PASSAGE PUBLICATION -> MODERATION
    // ======================================
    #[Route('/avis/{id}', name: 'avis_store', methods: ['GET','POST'])]
    public function store(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $reservationId = (int) $request->attributes->get('id');
        
        $note = (int) $request->request->get('note');
        $commentaire = trim($request->request->get('commentaire'));

        if ($reservationId <= 0 || $note < 1 || $note > 5 || $commentaire === '') {
            $this->addFlash('error', 'Champs invalides.');
            return $this->redirectToRoute('reservation_my');
        }

        $reservation = $this->reservationRepository->find($reservationId);

        if (!$reservation || $reservation->getPassager() !== $user) {
            $this->addFlash('error', 'Réservation invalide.');
            return $this->redirectToRoute('reservation_my');
        }

        $existing = $this->avisRepository->findOneBy([
            'reservation' => $reservation
        ]);

        if ($existing) {
            $this->addFlash('error', 'Avis déjà déposé.');
            return $this->redirectToRoute('reservation_my');
        }

        $trajet = $reservation->getTrajet();
        $conducteur = $trajet->getConducteur();

        $avis = new Avis();

        $avis->setReservation($reservation);
        $avis->setConducteur($conducteur);
        $avis->setPassager($user);
        $avis->setNote($note);
        $avis->setCommentaire($commentaire);
        $avis->setApprouve(false);

        $this->em->persist($avis);
        $this->em->flush();

        $this->addFlash('success', 'Merci, avis envoyé en modération.');

        return $this->redirectToRoute('reservation_my');
    }

}
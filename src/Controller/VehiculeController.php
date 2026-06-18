<?php

namespace App\Controller;

use App\Repository\PreferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class VehiculeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PreferenceRepository $preferenceRepository
    ) {}

    // =========================
    // CREATE FORM
    // =========================
    #[Route('/vehicules/creer', name: 'vehicule_create_form', methods: ['GET'])]
    public function new(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');

        $preferences = $this->preferenceRepository->findBy([], [
            'nom' => 'ASC'
        ]);

        return $this->render('vehicules/create.html.twig', [
            'preferences' => $preferences,
            'old' => [],
            'errors' => []
        ]);
    }

    // =========================
    // STORE VEHICULE
    // =========================
    #[Route('/vehicules/creer', name: 'vehicule_store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_CONDUCTEUR');

        $user = $this->getUser();

        $marque  = trim($request->request->get('marque'));
        $modele  = trim($request->request->get('modele'));
        $couleur = trim($request->request->get('couleur'));
        $energie = trim($request->request->get('energie'));
        $places  = (int) $request->request->get('places');
        $prefs = $request->request->all('preferences');

            if (!is_array($prefs)) {
                $prefs = [];
            }

        $errors = [];

        if (!$marque || !$modele || !$couleur || !$energie || $places <= 0) {
            $errors[] = "Tous les champs doivent être remplis correctement.";
        }

        if (!in_array($energie, ['essence', 'diesel', 'electrique'], true)) {
            $errors[] = "Type d'énergie invalide.";
        }

        if ($errors) {
            $preferences = $this->preferenceRepository->findAll();

            return $this->render('vehicules/create.html.twig', [
                'preferences' => $preferences,
                'errors' => $errors,
                'old' => $request->request->all()
            ]);
        }

        // =========================
        // CREATE VEHICULE
        // =========================
        $vehicule = new \App\Entity\Vehicule();
        $vehicule->setUser($user);
        $vehicule->setMarque($marque);
        $vehicule->setModele($modele);
        $vehicule->setCouleur($couleur);
        $vehicule->setEnergie($energie);
        $vehicule->setPlaces($places);

        // =========================
        // PREFERENCES (ManyToMany)
        // =========================
        foreach ($prefs as $prefId) {
        $pref = $this->preferenceRepository->find((int)$prefId);

            if ($pref) {
                $vehicule->addPreference($pref);
            }
        }

        $this->em->persist($vehicule);
        $this->em->flush();

        $this->addFlash('success', 'Véhicule ajouté.');

        return $this->redirectToRoute('profile_dashboard');
    }
}
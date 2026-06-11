<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmployeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {}

    // =========================
    // DASHBOARD EMPLOYÉ
    // =========================
    #[Route('/employe', name: 'employe_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $conn = $this->em->getConnection();

        // Avis à modérer
        $avis = $conn->fetchAllAssociative("
            SELECT a.id, a.commentaire, a.note,
                   u1.pseudo AS passager,
                   u2.pseudo AS chauffeur
            FROM avis a
            JOIN users u1 ON a.passager_id = u1.id
            JOIN users u2 ON a.conducteur_id = u2.id
            WHERE a.approuve = 0
            ORDER BY a.id DESC
        ");

        // Problèmes signalés
        $problemes = $conn->fetchAllAssociative("
            SELECT 
                a.id AS avis_id,
                p.pseudo AS passager,
                p.email AS email_passager,
                c.pseudo AS chauffeur,
                c.email AS email_chauffeur,
                t.ville_depart,
                t.ville_arrivee,
                t.date_depart,
                a.commentaire AS description
            FROM avis a
            JOIN trajets t ON a.conducteur_id = t.conducteur_id
            JOIN users p ON a.passager_id = p.id
            JOIN users c ON a.conducteur_id = c.id
            WHERE a.approuve = 1
              AND a.is_problem = 1
            ORDER BY a.id DESC
        ");

        return $this->render('employe/dashboard.html.twig', [
            'avis' => $avis,
            'problemes' => $problemes,
        ]);
    }

    // =========================
    // MODERATION
    // =========================
    #[Route('/employe/moderation', name: 'employe_moderate', methods: ['POST'])]
    public function moderate(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $avisId = (int) $request->request->get('avis_id');
        $action = $request->request->get('action');
        $isProblem = $request->request->has('is_problem') ? 1 : 0;

        if (!$avisId || !in_array($action, ['valider', 'refuser'], true)) {
            $this->addFlash('error', 'Requête invalide.');
            return $this->redirectToRoute('employe_dashboard');
        }

        $approuve = $action === 'valider' ? 1 : 0;

        $conn = $this->em->getConnection();
        $conn->executeStatement("
            UPDATE avis 
            SET approuve = :approuve,
                is_problem = :is_problem
            WHERE id = :id
        ", [
            'approuve' => $approuve,
            'is_problem' => $isProblem,
            'id' => $avisId
        ]);

        $this->addFlash('success', 'Avis mis à jour.');

        return $this->redirectToRoute('employe_dashboard');
    }
}
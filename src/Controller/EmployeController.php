<?php
namespace App\Controller;

use App\Form\PhotoProfilFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmployeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
        
    ) {}

    #[Route('/employe/profil', name: 'employe_profil', methods: ['GET', 'POST'])]
    public function profil(
        Request $request
    ):Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $user = $this->getUser();

        $form = $this->createForm(PhotoProfilFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();
        if (!$user instanceof \App\Entity\User) {
            throw new \LogicException('User attendu');
        }

        $file = $form->get('photo')->getData();

        if (!$file) {
            $this->addFlash('error', 'Erreur d\'upload.');
            return $this->redirectToRoute('employe_profil');
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower($file->guessExtension());

        if (!in_array($ext, $allowed, true)) {
            $this->addFlash('error', 'Type de fichier non autorisé.');
            return $this->redirectToRoute('employe_profil');
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            $this->addFlash('error', 'Fichier trop volumineux (max 2 Mo).');
            return $this->redirectToRoute('employe_profil');
        }

        $safeName = 'u' . $user->getId() . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        try {
            $file->move(
                $this->getParameter('photos_directory'),
                $safeName
            );
        } catch (FileException $e) {
            $this->addFlash('error', 'Impossible d\'enregistrer le fichier.');
            return $this->redirectToRoute('employe_profil');
        }

        $user->setPhoto($safeName);
        $this->em->flush();

        $this->addFlash('success', 'Photo mise à jour.');
        
        }
        return $this->render('employe/profil_employe.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
}
    #[Route('/employe/moderation', name: 'employe_moderate', methods: ['GET','POST'])]
    public function moderate()
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');
        return $this->redirectToRoute('admin_avis');
    }

    
}
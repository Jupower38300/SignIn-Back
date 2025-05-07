<?php
namespace App\Controller;

use App\Entity\Sessions;
use App\Form\SessionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SessionsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/sessions', name: 'session_list')]
    public function index(Request $request): Response
    {
        // Create a new session for the form
        $session = new Sessions();
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Set creation date or any default values if needed
            $session->setDateStart(new \DateTime());
            
            // Save the session
            $this->entityManager->persist($session);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Session créée avec succès!');
            
            return $this->redirectToRoute('session_list');
        }
        
        // Get all sessions for the list
        $sessions = $this->entityManager->getRepository(Sessions::class)->findAll();
        
        return $this->render('sessions/index.html.twig', [
            'sessions' => $sessions,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/session/{id}', name: 'session_show')]
    public function show(Sessions $session): Response
    {
        // Generate the QR code URL
        $qrCodeUrl = $this->generateUrl(
            'session_qrcode',
            ['id' => $session->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        
        return $this->render('sessions/qrcode.html.twig', [
            'session' => $session,
            'qrCodeUrl' => $qrCodeUrl
        ]);
    }
}
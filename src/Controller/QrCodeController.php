<?php
namespace App\Controller;

use App\Entity\Sessions;
use App\Service\TokenGenerator;
use BaconQrCode\Common\ErrorCorrectionLevel;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class QrCodeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/session/{id}/qrcode', name: 'session_qrcode')]
    public function showQrCode(
        TokenGenerator $tokenGenerator,
        int $id
    ): Response {
        $session = $this->entityManager->getRepository(Sessions::class)->find($id);
        if (!$session) {
            throw $this->createNotFoundException('Session non trouvée');
        }
    
        // Calcule l'intervalle actuel basé sur le temps Unix
        $currentInterval = $tokenGenerator->getCurrentInterval();
    
        // Génère le contenu du QR code
        $qrContent = sprintf(
            '%s|%s',
            $session->getId(),
            $tokenGenerator->generateToken($session->getId(), $currentInterval)
        );
    
        // Configure le style du QR Code
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            data: $qrContent,
            encoding: new Encoding('UTF-8'),  
    
            size: 400,
            margin: 20
        );
    
        $result = $builder->build();
    
        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            [
                'Content-Type' => $result->getMimeType(),
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ]
        );
    }
    
    // Modifiez la méthode showQrCodePage
    #[Route('/session/{id}/qrcode/page', name: 'session_qrcode_page')]
    public function showQrCodePage(int $id, TokenGenerator $tokenGenerator): Response
    {
        $session = $this->entityManager->getRepository(Sessions::class)->find($id);
        if (!$session) {
            throw $this->createNotFoundException('Session non trouvée');
        }
    
        // Fixe l'intervalle initial
        $initialInterval = $tokenGenerator->getCurrentInterval();
        $qrCodeUrl = $this->generateUrl('session_qrcode', [
            'id' => $id,
            'interval' => $initialInterval
        ]);
    
        return $this->render('session/qrcode.html.twig', [
            'session' => $session,
            'qrCodeUrl' => $qrCodeUrl,
            'initialInterval' => $initialInterval
        ]);
    }

}
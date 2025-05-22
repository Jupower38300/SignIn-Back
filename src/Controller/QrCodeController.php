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
        int $id,
        Request $request
    ): Response {
        $session = $this->entityManager->getRepository(Sessions::class)->find($id);
        if (!$session) {
            throw $this->createNotFoundException('Session non trouvée');
        }
    
        // Si on reçoit l'intervalle du client, on l'utilise ; sinon on calcule l'actuel
        $interval = $request->query->getInt('interval', $tokenGenerator->getCurrentInterval());
    
        $qrContent = sprintf(
            '%s|%s',
            $session->getId(),
            $tokenGenerator->generateToken($session->getId())
        );
    
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

    #[Route('/session/{id}/qrcode/page', name: 'session_qrcode_page')]
    public function showQrCodePage(int $id, TokenGenerator $tokenGenerator): Response
    {
        $session = $this->entityManager->getRepository(Sessions::class)->find($id);
        if (!$session) {
            throw $this->createNotFoundException('Session non trouvée');
        }
    
        $initialInterval = $tokenGenerator->getCurrentInterval();
        $qrCodeUrl = $this->generateUrl('session_qrcode', ['id' => $id]);
    
        return $this->render('sessions/qrcode.html.twig', [
            'session' => $session,
            'qrCodeUrl' => $qrCodeUrl,
            'initialInterval' => $initialInterval,
            'intervalDuration' => $tokenGenerator->getIntervalDuration() // en secondes
        ]);
    }
}
<?php
namespace App\Controller;

use App\Entity\Sessions;
use App\Service\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        // Retrieve the session
        $session = $this->entityManager->getRepository(Sessions::class)->find($id);
        if (!$session) {
            throw $this->createNotFoundException('The requested session does not exist');
        }

        // Generate secure content
        $qrContent = sprintf(
            '%s|%s',
            $session->getId(),
            $tokenGenerator->generateToken(
                $session->getId(),
                $tokenGenerator->getCurrentInterval()
            )
        );

        // Build the QR code (for Endroid QR Code 5.x)
        $builder = new Builder();
        $qrCode = $builder
            ->data($qrContent)
            ->size(400)
            ->margin(10)
            ->build();

        // Use the PNG writer to generate the image
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Return the image response
        return new Response(
            $result->getString(),
            Response::HTTP_OK,
            ['Content-Type' => $result->getMimeType()]
        );
    }
}
<?php
namespace App\Controller;

use App\Entity\Presences;
use App\Entity\Sessions;
use App\Entity\User;
use App\Service\TokenGenerator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private TokenGenerator $tokenGenerator;

    public function __construct(
        EntityManagerInterface $entityManager,
        TokenGenerator $tokenGenerator
    ) {
        $this->entityManager = $entityManager;
        $this->tokenGenerator = $tokenGenerator;
    }

    #[Route('/session/validate', name: 'validate_session', methods: ['POST'])]
    public function validateSession(Request $request): JsonResponse
    {
        try {
            // Récupérer le contenu de la requête JSON
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['sessionToken'])) {
                return $this->json(['error' => 'Token de session manquant'], Response::HTTP_BAD_REQUEST);
            }
            
            $token = $data['sessionToken'];
            
            // Analyser le token (format: sessionId|tokenValue)
            $parts = explode('|', $token);
            if (count($parts) !== 2) {
                return $this->json(['error' => 'Format de token invalide'], Response::HTTP_BAD_REQUEST);
            }
            
            $sessionId = $parts[0];
            $tokenValue = $parts[1];
            
            // Vérifier que la session existe
            $session = $this->entityManager->getRepository(Sessions::class)->find($sessionId);
            if (!$session) {
                return $this->json(['error' => 'Session non trouvée'], Response::HTTP_NOT_FOUND);
            }
            
            // Vérifier la validité du token pour cette session
            $currentInterval = $this->tokenGenerator->getCurrentInterval();
            $generatedToken = $this->tokenGenerator->generateToken($sessionId, $currentInterval);
            
            // Vérifier aussi le token précédent pour plus de tolérance
            $previousInterval = $currentInterval - 1;
            $previousToken = $this->tokenGenerator->generateToken($sessionId, $previousInterval);
            
            if ($tokenValue !== $generatedToken && $tokenValue !== $previousToken) {
                return $this->json([
                    'error' => 'Token invalide ou expiré',
                    'debug' => [
                        'provided' => $tokenValue,
                        'expected' => $generatedToken,
                        'previous' => $previousToken
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            // Retourner les informations de la session
            return $this->json([
                'sessionId' => $session->getId(),
                'sessionName' => $session->getNomSession()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur serveur: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/presences', name: 'register_presence', methods: ['POST'])]
    public function registerPresence(Request $request): JsonResponse
    {
        try {
            // Log de la requête brute en mode développement
            if ($this->getParameter('kernel.environment') === 'dev') {
                error_log('Raw request content: ' . $request->getContent());
            }
            
            // Récupérer le contenu de la requête JSON
            $data = json_decode($request->getContent(), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json([
                    'error' => 'Erreur de décodage JSON: ' . json_last_error_msg(),
                    'raw' => substr($request->getContent(), 0, 1000) // Afficher les premiers caractères
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Vérification des champs requis
            $requiredFields = ['firstName', 'lastName', 'email', 'signature', 'sessionToken'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || trim($data[$field]) === '') {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                return $this->json([
                    'error' => 'Données incomplètes',
                    'missingFields' => $missingFields
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Nettoyer les données
            foreach (['firstName', 'lastName', 'email', 'signature'] as $field) {
                $data[$field] = trim($data[$field]);
            }
            
            // Rechercher l'utilisateur par email
            $user = $this->entityManager->getRepository(User::class)->findOneBy([
                'email' => $data['email']
            ]);
            
            // Vérifier que les données correspondent à un utilisateur existant
            if (!$user) {
                return $this->json([
                    'error' => 'Utilisateur non trouvé',
                    'email' => $data['email']
                ], Response::HTTP_NOT_FOUND);
            }
            
            // Utiliser la casse insensible pour les comparaisons de prénom et nom
            if (strtolower($user->getFirstName()) !== strtolower($data['firstName']) || 
                strtolower($user->getLastName()) !== strtolower($data['lastName'])) {
                return $this->json([
                    'error' => 'Les informations d\'identité ne correspondent pas',
                    'provided' => [
                        'firstName' => $data['firstName'],
                        'lastName' => $data['lastName']
                    ],
                    'expected' => [
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName()
                    ]
                ], Response::HTTP_BAD_REQUEST);
            }
            
            // Analyser le token (format: sessionId|tokenValue)
            $token = $data['sessionToken'];
            $parts = explode('|', $token);
            if (count($parts) !== 2) {
                return $this->json(['error' => 'Format de token invalide'], Response::HTTP_BAD_REQUEST);
            }
            
            $sessionId = $parts[0];
            $tokenValue = $parts[1];
            
            // Vérifier que la session existe
            $session = $this->entityManager->getRepository(Sessions::class)->find($sessionId);
            if (!$session) {
                return $this->json(['error' => 'Session non trouvée'], Response::HTTP_NOT_FOUND);
            }
            
            // Vérifier la validité du token pour cette session
            $currentInterval = $this->tokenGenerator->getCurrentInterval();
            $generatedToken = $this->tokenGenerator->generateToken($sessionId, $currentInterval);
            
            // Vérifier aussi le token précédent pour plus de tolérance
            $previousInterval = $currentInterval - 1;
            $previousToken = $this->tokenGenerator->generateToken($sessionId, $previousInterval);
            
            if ($tokenValue !== $generatedToken && $tokenValue !== $previousToken) {
                return $this->json([
                    'error' => 'Token invalide ou expiré',
                    'debug' => [
                        'provided' => $tokenValue,
                        'expected' => $generatedToken,
                        'previous' => $previousToken
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
            
            // Vérifier si l'utilisateur est déjà présent dans cette session
            $existingPresence = $this->entityManager->getRepository(Presences::class)->findOneBy([
                'presence_user' => $user,
                'presences_session' => $session
            ]);
            
            if ($existingPresence) {
                return $this->json([
                    'warning' => 'Présence déjà enregistrée pour cet utilisateur',
                    'presenceId' => $existingPresence->getId(),
                    'timestamp' => $existingPresence->getHorodatage()->format('Y-m-d H:i:s')
                ], Response::HTTP_OK); // Retourne 200 OK au lieu de 409 Conflict
            }
            
            // Créer une nouvelle présence
            $presence = new Presences();
            $presence->setPresenceUser($user);
            $presence->setPresencesSession($session);
            $presence->setSignature($data['signature']);
            $presence->setHorodatage(new DateTime());
            
            // Persister l'entité
            $this->entityManager->persist($presence);
            $this->entityManager->flush();
            
            return $this->json([
                'success' => true,
                'message' => 'Présence enregistrée avec succès',
                'presenceId' => $presence->getId(),
                'timestamp' => $presence->getHorodatage()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur serveur: ' . $e->getMessage(),
                'trace' => $this->getParameter('kernel.environment') === 'dev' ? $e->getTraceAsString() : null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
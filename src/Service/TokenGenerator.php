<?php
namespace App\Service;

class TokenGenerator
{
    private string $secretKey;
    private int $intervalDuration;

    public function __construct(string $secretKey = 'your_secret_key_here', int $intervalDuration = 30)
    {
        $this->secretKey = $secretKey;
        $this->intervalDuration = $intervalDuration;
    }

    public function getCurrentInterval(): int
    {
        return 0; // Désactive la rotation temporelle
    }

    public function getRemainingTime(): int
    {
        return $this->intervalDuration; // Temps fixe
    }

    public function generateToken(int $sessionId, int $interval): string
    {
        // Version statique pour le debug
        return hash_hmac('sha256', (string)$sessionId, $this->secretKey, false);
    }
}
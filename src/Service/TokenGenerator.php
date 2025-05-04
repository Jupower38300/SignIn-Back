<?php

namespace App\Service;

class TokenGenerator
{
    private string $secretKey;
    private int $intervalDuration;

    public function __construct(string $secretKey = 'your_secret_key_here', int $intervalDuration = 300)
    {
        $this->secretKey = $secretKey;
        $this->intervalDuration = $intervalDuration; 
    }

    public function generateToken(int $sessionId, int $interval): string
    {
        $data = $sessionId . '|' . $interval . '|' . $this->secretKey;
        return hash('sha256', $data);
    }

    public function getCurrentInterval(): int
    {
        return (int)(time() / $this->intervalDuration);
    }

    public function validateToken(int $sessionId, string $token): bool
    {
        $currentInterval = $this->getCurrentInterval();
        
        $validTokens = [
            $this->generateToken($sessionId, $currentInterval),
            $this->generateToken($sessionId, $currentInterval - 1)
        ];

        return in_array($token, $validTokens);
    }
}
<?php
namespace App\Service;

class TokenGenerator
{
    private string $secretKey;
    private int $intervalDuration;

    public function __construct(string $secretKey = 'secret', int $intervalDuration = 2)
    {
        $this->secretKey = $secretKey;
        $this->intervalDuration = $intervalDuration;
    }

    public function getCurrentInterval(): int
    {
        return (int) floor(time() / $this->intervalDuration);
    }

    public function generateToken(string $sessionId): string
    {
        $interval = $this->getCurrentInterval();
        return hash_hmac('sha256', $sessionId . '|' . $interval, $this->secretKey);
    }

    public function getIntervalDuration(): int
    {
        return $this->intervalDuration;
    }

    public function getRemainingTime(): int
    {
        return $this->intervalDuration - (time() % $this->intervalDuration);
    }
}
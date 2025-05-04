<?php

namespace App\Entity;

use App\Repository\PresencesRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PresencesRepository::class)]
class Presences
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'presences')]
    private ?User $presence_user = null;

    #[ORM\ManyToOne(inversedBy: 'presences')]
    private ?Sessions $presences_session = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $signature = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $horodatage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getPresenceUser(): ?User
    {
        return $this->presence_user;
    }

    public function setPresenceUser(?User $presence_user): static
    {
        $this->presence_user = $presence_user;

        return $this;
    }

    public function getPresencesSession(): ?Sessions
    {
        return $this->presences_session;
    }

    public function setPresencesSession(?Sessions $presences_session): static
    {
        $this->presences_session = $presences_session;

        return $this;
    }

    public function getSignature(): ?string
    {
        return $this->signature;
    }

    public function setSignature(?string $signature): static
    {
        $this->signature = $signature;

        return $this;
    }

    public function getHorodatage(): ?\DateTimeInterface
    {
        return $this->horodatage;
    }

    public function setHorodatage(\DateTimeInterface $horodatage): static
    {
        $this->horodatage = $horodatage;

        return $this;
    }
}

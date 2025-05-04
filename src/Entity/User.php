<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Role;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', enumType: Role::class)]
    private ?Role $role;

    /**
     * @var Collection<int, Sessions>
     */
    #[ORM\OneToMany(targetEntity: Sessions::class, mappedBy: 'formateur')]
    private Collection $formateur;

    /**
     * @var Collection<int, Presences>
     */
    #[ORM\OneToMany(targetEntity: Presences::class, mappedBy: 'presence_user')]
    private Collection $presences;

    public function __construct()
    {
        $this->formateur = new ArrayCollection();
        $this->presences = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection<int, Sessions>
     */
    public function getFormateur(): Collection
    {
        return $this->formateur;
    }

    public function addFormateur(Sessions $formateur): static
    {
        if (!$this->formateur->contains($formateur)) {
            $this->formateur->add($formateur);
            $formateur->setFormateur($this);
        }

        return $this;
    }

    public function removeFormateur(Sessions $formateur): static
    {
        if ($this->formateur->removeElement($formateur)) {
            // set the owning side to null (unless already changed)
            if ($formateur->getFormateur() === $this) {
                $formateur->setFormateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Presences>
     */
    public function getPresences(): Collection
    {
        return $this->presences;
    }

    public function addPresence(Presences $presence): static
    {
        if (!$this->presences->contains($presence)) {
            $this->presences->add($presence);
            $presence->setPresenceUser($this);
        }

        return $this;
    }

    public function removePresence(Presences $presence): static
    {
        if ($this->presences->removeElement($presence)) {
            // set the owning side to null (unless already changed)
            if ($presence->getPresenceUser() === $this) {
                $presence->setPresenceUser(null);
            }
        }

        return $this;
    }
}

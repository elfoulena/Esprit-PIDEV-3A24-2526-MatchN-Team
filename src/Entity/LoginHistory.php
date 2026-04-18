<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class LoginHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private string $ip;

    #[ORM\Column(nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: "datetime")]
    private \DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $user;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
{
    return $this->id;
}

    public function getCountry(): ?string { return $this->country; }
    public function setCountry(?string $country): static { $this->country = $country; return $this; }

    public function getIp(): string { return $this->ip; }
    public function setIp(string $ip): static { $this->ip = $ip; return $this; }

    public function setUser(User $user): static { $this->user = $user; return $this; }

    public function getCreatedAt(): \DateTime { return $this->createdAt; }
}
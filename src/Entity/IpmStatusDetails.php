<?php

namespace App\Entity;

use App\Repository\IpmStatusDetailsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IpmStatusDetailsRepository::class)]
class IpmStatusDetails
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serverName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hostName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $scriptName = null;

    #[ORM\Column(nullable: true)]
    private ?int $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServerName(): ?string
    {
        return $this->serverName;
    }

    public function setServerName(?string $serverName): static
    {
        $this->serverName = $serverName;

        return $this;
    }

    public function getHostName(): ?string
    {
        return $this->hostName;
    }

    public function setHostName(?string $hostName): static
    {
        $this->hostName = $hostName;

        return $this;
    }

    public function getScriptName(): ?string
    {
        return $this->scriptName;
    }

    public function setScriptName(?string $scriptName): static
    {
        $this->scriptName = $scriptName;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\IpmTimerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IpmTimerRepository::class)]
class IpmTimer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $timerId = null;

    #[ORM\Column(nullable: true)]
    private ?int $requestId = null;

    #[ORM\Column(nullable: true)]
    private ?int $hitCount = null;

    #[ORM\Column(nullable: true)]
    private ?float $value = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tagName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tagValue = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimerId(): ?int
    {
        return $this->timerId;
    }

    public function setTimerId(?int $timerId): static
    {
        $this->timerId = $timerId;

        return $this;
    }

    public function getRequestId(): ?int
    {
        return $this->requestId;
    }

    public function setRequestId(?int $requestId): static
    {
        $this->requestId = $requestId;

        return $this;
    }

    public function getHitCount(): ?int
    {
        return $this->hitCount;
    }

    public function setHitCount(?int $hitCount): static
    {
        $this->hitCount = $hitCount;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(?float $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getTagName(): ?string
    {
        return $this->tagName;
    }

    public function setTagName(?string $tagName): static
    {
        $this->tagName = $tagName;

        return $this;
    }

    public function getTagValue(): ?string
    {
        return $this->tagValue;
    }

    public function setTagValue(?string $tagValue): static
    {
        $this->tagValue = $tagValue;

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

<?php

namespace App\Entity;

use App\Repository\IpmTagInfoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IpmTagInfoRepository::class)]
class IpmTagInfo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $groupTI = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $server = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serverName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hostName = null;

    #[ORM\Column(nullable: true)]
    private ?int $reqCount = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqPerSec = null;

    #[ORM\Column(nullable: true)]
    private ?int $hitCount = null;

    #[ORM\Column(nullable: true)]
    private ?int $hitPerSec = null;

    #[ORM\Column(nullable: true)]
    private ?float $timerValue = null;

    #[ORM\Column(nullable: true)]
    private ?float $timerMedian = null;

    #[ORM\Column(nullable: true)]
    private ?float $ruUtimeValue = null;

    #[ORM\Column(nullable: true)]
    private ?float $ruStimeValue = null;

    #[ORM\Column(nullable: true)]
    private ?float $p90 = null;

    #[ORM\Column(nullable: true)]
    private ?float $p95 = null;

    #[ORM\Column(nullable: true)]
    private ?float $p99 = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getGroupTI(): ?string
    {
        return $this->groupTI;
    }

    public function setGroupTI(?string $groupTI): static
    {
        $this->groupTI = $groupTI;

        return $this;
    }

    public function getServer(): ?string
    {
        return $this->server;
    }

    public function setServer(?string $server): static
    {
        $this->server = $server;

        return $this;
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

    public function getReqCount(): ?int
    {
        return $this->reqCount;
    }

    public function setReqCount(?int $reqCount): static
    {
        $this->reqCount = $reqCount;

        return $this;
    }

    public function getReqPerSec(): ?float
    {
        return $this->reqPerSec;
    }

    public function setReqPerSec(?float $reqPerSec): static
    {
        $this->reqPerSec = $reqPerSec;

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

    public function getHitPerSec(): ?int
    {
        return $this->hitPerSec;
    }

    public function setHitPerSec(?int $hitPerSec): static
    {
        $this->hitPerSec = $hitPerSec;

        return $this;
    }

    public function getTimerValue(): ?float
    {
        return $this->timerValue;
    }

    public function setTimerValue(?float $timerValue): static
    {
        $this->timerValue = $timerValue;

        return $this;
    }

    public function getTimerMedian(): ?float
    {
        return $this->timerMedian;
    }

    public function setTimerMedian(?float $timerMedian): static
    {
        $this->timerMedian = $timerMedian;

        return $this;
    }

    public function getRuUtimeValue(): ?float
    {
        return $this->ruUtimeValue;
    }

    public function setRuUtimeValue(?float $ruUtimeValue): static
    {
        $this->ruUtimeValue = $ruUtimeValue;

        return $this;
    }

    public function getRuStimeValue(): ?float
    {
        return $this->ruStimeValue;
    }

    public function setRuStimeValue(?float $ruStimeValue): static
    {
        $this->ruStimeValue = $ruStimeValue;

        return $this;
    }

    public function getP90(): ?float
    {
        return $this->p90;
    }

    public function setP90(?float $p90): static
    {
        $this->p90 = $p90;

        return $this;
    }

    public function getP95(): ?float
    {
        return $this->p95;
    }

    public function setP95(?float $p95): static
    {
        $this->p95 = $p95;

        return $this;
    }

    public function getP99(): ?float
    {
        return $this->p99;
    }

    public function setP99(?float $p99): static
    {
        $this->p99 = $p99;

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

<?php

namespace App\Entity;

use App\Repository\IpmReportByHostnameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IpmReportByHostnameRepository::class)]
class IpmReportByHostname
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?int $reqCount = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqPerSec = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqTimeTotal = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqTimePercent = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqTimePerSec = null;

    #[ORM\Column(nullable: true)]
    private ?float $ruUtimeTotal = null;

    #[ORM\Column(nullable: true)]
    private ?float $ruUtimePercent = null;

    #[ORM\Column(nullable: true)]
    private ?float $ruUtimePerSec = null;

    #[ORM\Column(nullable: true)]
    private ?float $riStimeTotal = null;

    #[ORM\Column(nullable: true)]
    private ?float $ruStimePercent = null;

    #[ORM\Column(nullable: true)]
    private ?float $ruStimePerSec = null;

    #[ORM\Column(nullable: true)]
    private ?float $trafficTotal = null;

    #[ORM\Column(nullable: true)]
    private ?float $trafficPercent = null;

    #[ORM\Column(nullable: true)]
    private ?float $trafficPerSec = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hostName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getReqTimeTotal(): ?float
    {
        return $this->reqTimeTotal;
    }

    public function setReqTimeTotal(?float $reqTimeTotal): static
    {
        $this->reqTimeTotal = $reqTimeTotal;

        return $this;
    }

    public function getReqTimePercent(): ?float
    {
        return $this->reqTimePercent;
    }

    public function setReqTimePercent(?float $reqTimePercent): static
    {
        $this->reqTimePercent = $reqTimePercent;

        return $this;
    }

    public function getReqTimePerSec(): ?float
    {
        return $this->reqTimePerSec;
    }

    public function setReqTimePerSec(?float $reqTimePerSec): static
    {
        $this->reqTimePerSec = $reqTimePerSec;

        return $this;
    }

    public function getRuUtimeTotal(): ?float
    {
        return $this->ruUtimeTotal;
    }

    public function setRuUtimeTotal(?float $ruUtimeTotal): static
    {
        $this->ruUtimeTotal = $ruUtimeTotal;

        return $this;
    }

    public function getRuUtimePercent(): ?float
    {
        return $this->ruUtimePercent;
    }

    public function setRuUtimePercent(?float $ruUtimePercent): static
    {
        $this->ruUtimePercent = $ruUtimePercent;

        return $this;
    }

    public function getRuUtimePerSec(): ?float
    {
        return $this->ruUtimePerSec;
    }

    public function setRuUtimePerSec(?float $ruUtimePerSec): static
    {
        $this->ruUtimePerSec = $ruUtimePerSec;

        return $this;
    }

    public function getRiStimeTotal(): ?float
    {
        return $this->riStimeTotal;
    }

    public function setRiStimeTotal(?float $riStimeTotal): static
    {
        $this->riStimeTotal = $riStimeTotal;

        return $this;
    }

    public function getRuStimePercent(): ?float
    {
        return $this->ruStimePercent;
    }

    public function setRuStimePercent(?float $ruStimePercent): static
    {
        $this->ruStimePercent = $ruStimePercent;

        return $this;
    }

    public function getRuStimePerSec(): ?float
    {
        return $this->ruStimePerSec;
    }

    public function setRuStimePerSec(?float $ruStimePerSec): static
    {
        $this->ruStimePerSec = $ruStimePerSec;

        return $this;
    }

    public function getTrafficTotal(): ?float
    {
        return $this->trafficTotal;
    }

    public function setTrafficTotal(?float $trafficTotal): static
    {
        $this->trafficTotal = $trafficTotal;

        return $this;
    }

    public function getTrafficPercent(): ?float
    {
        return $this->trafficPercent;
    }

    public function setTrafficPercent(?float $trafficPercent): static
    {
        $this->trafficPercent = $trafficPercent;

        return $this;
    }

    public function getTrafficPerSec(): ?float
    {
        return $this->trafficPerSec;
    }

    public function setTrafficPerSec(?float $trafficPerSec): static
    {
        $this->trafficPerSec = $trafficPerSec;

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

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(?string $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}

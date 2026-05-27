<?php

namespace App\Entity;

use App\Repository\IpmPinbaReportByHostName_90_95_99Repository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IpmPinbaReportByHostName_90_95_99Repository::class)]
class IpmPinbaReportByHostname_90_95_99
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
    private ?float $ruStimeTotal = null;

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

    #[ORM\Column(nullable: true)]
    private ?float $memoryFootprintTotal = null;

    #[ORM\Column(nullable: true)]
    private ?float $memoryFootprintPercent = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqTimeMedian = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $indexValue = null;

    #[ORM\Column(nullable: true)]
    private ?float $p90 = null;

    #[ORM\Column(nullable: true)]
    private ?float $p95 = null;

    #[ORM\Column(nullable: true)]
    private ?float $p99 = null;

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

    public function getRuStimeTotal(): ?float
    {
        return $this->ruStimeTotal;
    }

    public function setRuStimeTotal(?float $ruStimeTotal): static
    {
        $this->ruStimeTotal = $ruStimeTotal;

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

    public function getMemoryFootprintTotal(): ?float
    {
        return $this->memoryFootprintTotal;
    }

    public function setMemoryFootprintTotal(?float $memoryFootprintTotal): static
    {
        $this->memoryFootprintTotal = $memoryFootprintTotal;

        return $this;
    }

    public function getMemoryFootprintPercent(): ?float
    {
        return $this->memoryFootprintPercent;
    }

    public function setMemoryFootprintPercent(?float $memoryFootprintPercent): static
    {
        $this->memoryFootprintPercent = $memoryFootprintPercent;

        return $this;
    }

    public function getReqTimeMedian(): ?float
    {
        return $this->reqTimeMedian;
    }

    public function setReqTimeMedian(?float $reqTimeMedian): static
    {
        $this->reqTimeMedian = $reqTimeMedian;

        return $this;
    }

    public function getIndexValue(): ?string
    {
        return $this->indexValue;
    }

    public function setIndexValue(?string $indexValue): static
    {
        $this->indexValue = $indexValue;

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
}

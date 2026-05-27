<?php

namespace App\Entity;

use App\Repository\IpmPinbaTagInfoCategoryServerNameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IpmPinbaTagInfoCategoryServerNameRepository::class)]
class IpmPinbaTagInfoCategoryServerName
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tag1Value = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tag2Value = null;

    #[ORM\Column(nullable: true)]
    private ?int $reqCount = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqPerSec = null;

    #[ORM\Column(nullable: true)]
    private ?int $hitCount = null;

    #[ORM\Column(nullable: true)]
    private ?float $hitPerSec = null;

    #[ORM\Column(nullable: true)]
    private ?float $timerValue = null;

    #[ORM\Column(nullable: true)]
    private ?float $timerMedian = null;

    #[ORM\Column(nullable: true)]
    private ?float $ruUtimeValue = null;

    #[ORM\Column(nullable: true)]
    private ?float $ruStimeValue = null;

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

    public function getTag1Value(): ?string
    {
        return $this->tag1Value;
    }

    public function setTag1Value(?string $tag1Value): static
    {
        $this->tag1Value = $tag1Value;

        return $this;
    }

    public function getTag2Value(): ?string
    {
        return $this->tag2Value;
    }

    public function setTag2Value(?string $tag2Value): static
    {
        $this->tag2Value = $tag2Value;

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

    public function getHitPerSec(): ?float
    {
        return $this->hitPerSec;
    }

    public function setHitPerSec(?float $hitPerSec): static
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

    public function setP99(?float $p99): void
    {
        $this->p99 = $p99;
    }
}

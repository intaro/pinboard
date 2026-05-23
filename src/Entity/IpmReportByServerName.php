<?php

namespace App\Entity;

use App\Repository\IpmReportByServerNameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IpmReportByServerNameRepository::class)]
class IpmReportByServerName
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;
    #[ORM\Column(nullable: true)]
    private ?int $req_count = null;

    #[ORM\Column(nullable: true)]
    private ?float $req_per_sec = null;

    #[ORM\Column(nullable: true)]
    private ?float $req_time_total = null;

    #[ORM\Column(nullable: true)]
    private ?float $req_time_percent = null;

    #[ORM\Column(nullable: true)]
    private ?float $req_time_per_sec = null;

    #[ORM\Column(nullable: true)]
    private ?float $ru_utime_total = null;

    #[ORM\Column(nullable: true)]
    private ?float $ru_utime_percent = null;

    #[ORM\Column(nullable: true)]
    private ?float $ru_utime_per_sec = null;

    #[ORM\Column(nullable: true)]
    private ?float $ru_stime_total = null;

    #[ORM\Column(nullable: true)]
    private ?float $ru_stime_percent = null;

    #[ORM\Column(nullable: true)]
    private ?float $ru_stime_per_sec = null;

    #[ORM\Column(nullable: true)]
    private ?float $traffic_total = null;

    #[ORM\Column(nullable: true)]
    private ?float $traffic_percent = null;

    #[ORM\Column(nullable: true)]
    private ?float $traffic_per_sec = null;

    #[ORM\Column(nullable: true)]
    private ?string $server_name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $created_at = null;

    public function getReqCount(): ?int
    {
        return $this->req_count;
    }

    public function setReqCount(?int $req_count): static
    {
        $this->req_count = $req_count;

        return $this;
    }

    public function getReqPerSec(): ?float
    {
        return $this->req_per_sec;
    }

    public function setReqPerSec(?float $req_per_sec): static
    {
        $this->req_per_sec = $req_per_sec;

        return $this;
    }

    public function getReqTimeTotal(): ?float
    {
        return $this->req_time_total;
    }

    public function setReqTimeTotal(?float $req_time_total): static
    {
        $this->req_time_total = $req_time_total;

        return $this;
    }

    public function getReqTimePercent(): ?float
    {
        return $this->req_time_percent;
    }

    public function setReqTimePercent(?float $req_time_percent): static
    {
        $this->req_time_percent = $req_time_percent;

        return $this;
    }

    public function getReqTimePerSec(): ?float
    {
        return $this->req_time_per_sec;
    }

    public function setReqTimePerSec(?float $req_time_per_sec): static
    {
        $this->req_time_per_sec = $req_time_per_sec;

        return $this;
    }

    public function getRuUtimeTotal(): ?float
    {
        return $this->ru_utime_total;
    }

    public function setRuUtimeTotal(?float $ru_utime_total): static
    {
        $this->ru_utime_total = $ru_utime_total;

        return $this;
    }

    public function getRuUtimePercent(): ?float
    {
        return $this->ru_utime_percent;
    }

    public function setRuUtimePercent(?float $ru_utime_percent): static
    {
        $this->ru_utime_percent = $ru_utime_percent;

        return $this;
    }

    public function getRuUtimePerSec(): ?float
    {
        return $this->ru_utime_per_sec;
    }

    public function setRuUtimePerSec(?float $ru_utime_per_sec): static
    {
        $this->ru_utime_per_sec = $ru_utime_per_sec;

        return $this;
    }

    public function getRuStimeTotal(): ?float
    {
        return $this->ru_stime_total;
    }

    public function setRuStimeTotal(?float $ru_stime_total): static
    {
        $this->ru_stime_total = $ru_stime_total;

        return $this;
    }

    public function getRuStimePercent(): ?float
    {
        return $this->ru_stime_percent;
    }

    public function setRuStimePercent(?float $ru_stime_percent): static
    {
        $this->ru_stime_percent = $ru_stime_percent;

        return $this;
    }

    public function getRuStimePerSec(): ?float
    {
        return $this->ru_stime_per_sec;
    }

    public function setRuStimePerSec(?float $ru_stime_per_sec): static
    {
        $this->ru_stime_per_sec = $ru_stime_per_sec;

        return $this;
    }

    public function getTrafficTotal(): ?float
    {
        return $this->traffic_total;
    }

    public function setTrafficTotal(?float $traffic_total): static
    {
        $this->traffic_total = $traffic_total;

        return $this;
    }

    public function getTrafficPercent(): ?float
    {
        return $this->traffic_percent;
    }

    public function setTrafficPercent(?float $traffic_percent): static
    {
        $this->traffic_percent = $traffic_percent;

        return $this;
    }

    public function getTrafficPerSec(): ?float
    {
        return $this->traffic_per_sec;
    }

    public function setTrafficPerSec(?float $traffic_per_sec): static
    {
        $this->traffic_per_sec = $traffic_per_sec;

        return $this;
    }

    public function getServerName(): ?string
    {
        return $this->server_name;
    }

    public function setServerName(?string $server_name): static
    {
        $this->server_name = $server_name;

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

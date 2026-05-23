<?php

namespace App\Entity;

use App\Repository\ipmReport_2ByHostnameAndServerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ipmReport_2ByHostnameAndServerRepository::class)]
class ipmReport_2ByHostnameAndServer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serverName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hostName = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqTime90 = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqTime95 = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqTime99 = null;

    #[ORM\Column(nullable: true)]
    private ?float $reqTime100 = null;

    #[ORM\Column(nullable: true)]
    private ?float $memPeakUsage90 = null;

    #[ORM\Column(nullable: true)]
    private ?float $memPeakUsage95 = null;

    #[ORM\Column(nullable: true)]
    private ?float $memPeakUsage99 = null;

    #[ORM\Column(nullable: true)]
    private ?float $memPeakUsage100 = null;

    #[ORM\Column(nullable: true)]
    private ?float $docSize90 = null;

    #[ORM\Column(nullable: true)]
    private ?float $docSize95 = null;

    #[ORM\Column(nullable: true)]
    private ?float $docSize99 = null;

    #[ORM\Column(nullable: true)]
    private ?float $docSize100 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $created_at = null;

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

    public function getReqTime90(): ?float
    {
        return $this->reqTime90;
    }

    public function setReqTime90(?float $reqTime90): static
    {
        $this->reqTime90 = $reqTime90;

        return $this;
    }

    public function getReqTime95(): ?float
    {
        return $this->reqTime95;
    }

    public function setReqTime95(?float $reqTime95): static
    {
        $this->reqTime95 = $reqTime95;

        return $this;
    }

    public function getReqTime99(): ?float
    {
        return $this->reqTime99;
    }

    public function setReqTime99(?float $reqTime99): static
    {
        $this->reqTime99 = $reqTime99;

        return $this;
    }

    public function getReqTime100(): ?float
    {
        return $this->reqTime100;
    }

    public function setReqTime100(?float $reqTime100): static
    {
        $this->reqTime100 = $reqTime100;

        return $this;
    }

    public function getMemPeakUsage90(): ?float
    {
        return $this->memPeakUsage90;
    }

    public function setMemPeakUsage90(?float $memPeakUsage90): static
    {
        $this->memPeakUsage90 = $memPeakUsage90;

        return $this;
    }

    public function getMemPeakUsage95(): ?float
    {
        return $this->memPeakUsage95;
    }

    public function setMemPeakUsage95(?float $memPeakUsage95): static
    {
        $this->memPeakUsage95 = $memPeakUsage95;

        return $this;
    }

    public function getMemPeakUsage99(): ?float
    {
        return $this->memPeakUsage99;
    }

    public function setMemPeakUsage99(?float $memPeakUsage99): static
    {
        $this->memPeakUsage99 = $memPeakUsage99;

        return $this;
    }

    public function getMemPeakUsage100(): ?float
    {
        return $this->memPeakUsage100;
    }

    public function setMemPeakUsage100(?float $memPeakUsage100): static
    {
        $this->memPeakUsage100 = $memPeakUsage100;

        return $this;
    }

    public function getDocSize90(): ?float
    {
        return $this->docSize90;
    }

    public function setDocSize90(?float $docSize90): static
    {
        $this->docSize90 = $docSize90;

        return $this;
    }

    public function getDocSize95(): ?float
    {
        return $this->docSize95;
    }

    public function setDocSize95(?float $docSize95): static
    {
        $this->docSize95 = $docSize95;

        return $this;
    }

    public function getDocSize99(): ?float
    {
        return $this->docSize99;
    }

    public function setDocSize99(?float $docSize99): static
    {
        $this->docSize99 = $docSize99;

        return $this;
    }

    public function getDocSize100(): ?float
    {
        return $this->docSize100;
    }

    public function setDocSize100(?float $docSize100): static
    {
        $this->docSize100 = $docSize100;

        return $this;
    }

    public function getCreatedAt(): ?string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}

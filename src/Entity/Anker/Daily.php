<?php

namespace App\Entity\Anker;

use App\Repository\Anker\DailyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DailyRepository::class), ORM\Table(name: 'anker_daily')]
class Daily
{
    #[ORM\Id]
    #[ORM\Column(type: Types::DATE_MUTABLE, unique: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $batteryDischarge = null;

    #[ORM\Column]
    private ?int $homeUsage = null;

    #[ORM\Column]
    private ?int $gridToHome = null;

    #[ORM\Column]
    private ?int $solarProduction = null;

    #[ORM\Column]
    private ?int $batteryCharge = null;

    #[ORM\Column]
    private ?int $solarToGrid = null;

    #[ORM\Column]
    private ?int $batteryPercentage = null;

    #[ORM\Column]
    private ?int $solarPercentage = null;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function getBatteryDischarge(): ?int
    {
        return $this->batteryDischarge;
    }

    public function setBatteryDischarge(int $batteryDischarge): static
    {
        $this->batteryDischarge = $batteryDischarge;

        return $this;
    }

    public function getHomeUsage(): ?int
    {
        return $this->homeUsage;
    }

    public function setHomeUsage(int $homeUsage): static
    {
        $this->homeUsage = $homeUsage;

        return $this;
    }

    public function getGridToHome(): ?int
    {
        return $this->gridToHome;
    }

    public function setGridToHome(int $gridToHome): static
    {
        $this->gridToHome = $gridToHome;

        return $this;
    }

    public function getSolarProduction(): ?int
    {
        return $this->solarProduction;
    }

    public function setSolarProduction(int $solarProduction): static
    {
        $this->solarProduction = $solarProduction;

        return $this;
    }

    public function getBatteryCharge(): ?int
    {
        return $this->batteryCharge;
    }

    public function setBatteryCharge(int $batteryCharge): static
    {
        $this->batteryCharge = $batteryCharge;

        return $this;
    }

    public function getSolarToGrid(): ?int
    {
        return $this->solarToGrid;
    }

    public function setSolarToGrid(int $solarToGrid): static
    {
        $this->solarToGrid = $solarToGrid;

        return $this;
    }

    public function getBatteryPercentage(): ?int
    {
        return $this->batteryPercentage;
    }

    public function setBatteryPercentage(int $batteryPercentage): static
    {
        $this->batteryPercentage = $batteryPercentage;

        return $this;
    }

    public function getSolarPercentage(): ?int
    {
        return $this->solarPercentage;
    }

    public function setSolarPercentage(int $solarPercentage): static
    {
        $this->solarPercentage = $solarPercentage;

        return $this;
    }
}

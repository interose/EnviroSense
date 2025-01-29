<?php

namespace App\Entity\Anker;

use App\Repository\Anker\HourlyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HourlyRepository::class), ORM\Table(name: 'anker_hourly')]
class Hourly
{
    #[ORM\Id]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, unique: true)]
    private ?\DateTimeInterface $ts = null;

    #[ORM\Column(length: 10)]
    private ?string $powerUnit = null;

    #[ORM\Column]
    private ?int $solarPower1 = null;

    #[ORM\Column]
    private ?int $solarPower2 = null;

    #[ORM\Column]
    private ?int $solarPower3 = null;

    #[ORM\Column]
    private ?int $solarPower4 = null;

    #[ORM\Column]
    private ?int $batterySoc = null;

    #[ORM\Column]
    private ?int $batteryEnergy = null;

    #[ORM\Column]
    private ?int $chargingPower = null;

    public function getTs(): ?\DateTimeInterface
    {
        return $this->ts;
    }

    public function getPowerUnit(): ?string
    {
        return $this->powerUnit;
    }

    public function setPowerUnit(string $powerUnit): static
    {
        $this->powerUnit = $powerUnit;

        return $this;
    }

    public function getSolarPower1(): ?int
    {
        return $this->solarPower1;
    }

    public function setSolarPower1(int $solarPower1): static
    {
        $this->solarPower1 = $solarPower1;

        return $this;
    }

    public function getSolarPower2(): ?int
    {
        return $this->solarPower2;
    }

    public function setSolarPower2(int $solarPower2): static
    {
        $this->solarPower2 = $solarPower2;

        return $this;
    }

    public function getSolarPower3(): ?int
    {
        return $this->solarPower3;
    }

    public function setSolarPower3(int $solarPower3): static
    {
        $this->solarPower3 = $solarPower3;

        return $this;
    }

    public function getSolarPower4(): ?int
    {
        return $this->solarPower4;
    }

    public function setSolarPower4(int $solarPower4): static
    {
        $this->solarPower4 = $solarPower4;

        return $this;
    }

    public function getBatterySoc(): ?int
    {
        return $this->batterySoc;
    }

    public function setBatterySoc(int $batterySoc): static
    {
        $this->batterySoc = $batterySoc;

        return $this;
    }

    public function getBatteryEnergy(): ?int
    {
        return $this->batteryEnergy;
    }

    public function setBatteryEnergy(int $batteryEnergy): static
    {
        $this->batteryEnergy = $batteryEnergy;

        return $this;
    }

    public function getChargingPower(): ?int
    {
        return $this->chargingPower;
    }

    public function setChargingPower(int $chargingPower): static
    {
        $this->chargingPower = $chargingPower;

        return $this;
    }
}

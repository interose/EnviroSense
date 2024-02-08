<?php

namespace App\Entity\Power;

use App\Repository\Power\HourlyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HourlyRepository::class), ORM\Table(name: 'power_hourly')]
class Hourly
{
    #[ORM\Id]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $ts = null;

    #[ORM\Column]
    private ?int $value = null;

    #[ORM\Column]
    private ?int $scaler = null;

    public function getTs(): ?\DateTimeInterface
    {
        return $this->ts;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getScaler(): ?int
    {
        return $this->scaler;
    }

    public function setScaler(int $scaler): self
    {
        $this->scaler = $scaler;

        return $this;
    }
}

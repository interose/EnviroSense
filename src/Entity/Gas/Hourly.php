<?php

namespace App\Entity\Gas;

use App\Repository\Gas\HourlyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HourlyRepository::class), ORM\Table(name: 'gas_hourly')]
class Hourly
{
    #[ORM\Id]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, unique: true)]
    private ?\DateTimeInterface $ts = null;

    #[ORM\Column]
    private ?int $value = null;

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
}

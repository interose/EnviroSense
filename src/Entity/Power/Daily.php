<?php

namespace App\Entity\Power;

use App\Repository\Power\DailyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DailyRepository::class), ORM\Table(name: 'power_daily')]
class Daily
{
    #[ORM\Id]
    #[ORM\Column(type: Types::DATE_MUTABLE, unique: true)]
    private ?\DateTimeInterface $ts = null;

    #[ORM\Column(nullable: true)]
    private ?int $value = null;

    #[ORM\Column(nullable: true)]
    private ?int $scaler = null;

    #[ORM\Column(nullable: true)]
    private ?int $total = null;

    public function getTs(): ?\DateTimeInterface
    {
        return $this->ts;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(?int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getScaler(): ?int
    {
        return $this->scaler;
    }

    public function setScaler(?int $scaler): self
    {
        $this->scaler = $scaler;

        return $this;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): self
    {
        $this->total = $total;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\DayOffRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DayOffRepository::class)]
class DayOff
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $dayOff = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayOff(): ?\DateTimeInterface
    {
        return $this->dayOff;
    }

    public function setDayOff(\DateTimeInterface $dayOff): static
    {
        $this->dayOff = $dayOff;

        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $serviceName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $serviceContent = null;

    #[ORM\Column(nullable: true)]
    private ?float $servicePrice = null;

    #[ORM\ManyToOne(inversedBy: 'services')]
    private ?Category $category = null;

    /**
     * @var Collection<int, appointment>
     */
    #[ORM\ManyToMany(targetEntity: Appointment::class, inversedBy: 'services', cascade: ["persist"])]
    private Collection $appointments;

    public function __construct()
    {
        $this->appointments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    public function setServiceName(string $serviceName): static
    {
        $this->serviceName = $serviceName;

        return $this;
    }

    public function getServiceContent(): ?string
    {
        return $this->serviceContent;
    }

    public function setServiceContent(?string $serviceContent): static
    {
        $this->serviceContent = $serviceContent;

        return $this;
    }

    public function getServicePrice(): ?float
    {
        return $this->servicePrice;
    }

    public function setServicePrice(?float $servicePrice): static
    {
        $this->servicePrice = $servicePrice;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, appointment>
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(appointment $appointment): static
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments->add($appointment);
        }

        return $this;
    }

    public function removeAppointment(appointment $appointment): static
    {
        $this->appointments->removeElement($appointment);

        return $this;
    }
    public function __toString()
    {
        return $this -> serviceName;
    }
}

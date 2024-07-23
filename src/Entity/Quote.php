<?php

namespace App\Entity;

use App\Repository\QuoteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QuoteRepository::class)]
class Quote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $quoteDate = null;

    #[ORM\Column(nullable: true)]
    private ?float $totalTTC = null;

    #[ORM\OneToOne(inversedBy: 'quote', cascade: ['persist', 'remove'])]
    private ?Appointment $appointments = null;

    #[ORM\Column(length: 255)]
    private ?string $customerName = null;

    #[ORM\Column(length: 255)]
    private ?string $customerEmail = null;

    #[ORM\Column(length: 255)]
    private ?string $customerFirstName = null;
    #[ORM\Column(length: 255)]
    private ?string $pdfContent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getQuoteDate(): ?\DateTimeInterface
    {
        return $this->quoteDate;
    }

    public function setQuoteDate(\DateTimeInterface $quoteDate): static
    {
        $this->quoteDate = $quoteDate;

        return $this;
    }
    public function getAppointments(): ?Appointment
    {
        return $this->appointments;
    }

    public function setAppointments(?Appointment $appointments): static
    {
        $this->appointments = $appointments;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): static
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerEmail(): ?string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): static
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    public function getCustomerFirstName(): ?string
    {
        return $this->customerFirstName;
    }

    public function setCustomerFirstName(string $customerFirstName): static
    {
        $this->customerFirstName = $customerFirstName;

        return $this;
    }
    
    public function getTotalTTC(): ?float
    {
        return $this->totalTTC;
    }

    public function setTotalTTC(?float $totalTTC): static
    {
        $this->totalTTC = $totalTTC;

        return $this;
    }

    public function getPdfContent(): ?string
    {
        return $this->pdfContent;
    }

    public function setPdfContent(string $pdfContent): static
    {
        $this->pdfContent = $pdfContent;

        return $this;
    }
}

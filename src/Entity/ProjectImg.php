<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProjectImgRepository;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: ProjectImgRepository::class)]
class ProjectImg
{
    // ---------------------------------ATTRIBUTS--------------------------------- //
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    #[NotBlank(message: "L'image ne peut pas être vide.")]
    #[File(
        mimeTypes: ["image/webp", "image/png", "image/jpeg", "image/jpg"],
        mimeTypesMessage: "Veuillez télécharger une image au format valide (WebP, PNG, JPG, JPEG)."
    )]
    private ?string $image = null;


    #[ORM\Column(length: 255)]
    private ?string $alt = null;


    #[ORM\ManyToOne(inversedBy: 'images')]
    private ?Project $project = null;
    
    
    // ---------------------------------GETTERS AND SETTERS--------------------------------- //

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function setAlt(string $alt): static
    {
        $this->alt = $alt;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function __toString()
    {
        return $this -> image;
    }
}

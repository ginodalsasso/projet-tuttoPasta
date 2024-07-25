<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CommentRepository;
use Symfony\Component\Validator\Constraints as Assert; //use Assert pour les contraintes formulaire

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    // ---------------------------------ATTRIBUTS--------------------------------- //
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(type: Types::TEXT)]
    //use Assert pour les contraintes formulaire
    #[Assert\NotBlank(message: 'Le commentaire ne peut pas être vide')]
    #[Assert\Length(
        min: 5,
        max: 5000,
        minMessage: 'Le commentaire doit contenir au moins 5 caractères !',
        maxMessage: 'Le commentaire doit contenir au maximum 5000 caractères !')]
    private ?string $commentContent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $commentDate = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Article $article = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?User $user = null;

    
    // ---------------------------------CONSTRUCT--------------------------------- //


    public function __construct()
    {
        //initialise la date et l'heure du commentaire lors de la création de l'objet
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->commentDate = new \DateTime('now', $timezone);
    }

    // ---------------------------------GETTERS AND SETTERS--------------------------------- //

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUsername(): ?string
    {
        return $this->username;
    }


    public function setUsername(?string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getCommentContent(): ?string
    {
        return $this->commentContent;
    }

    public function setCommentContent(string $commentContent): static
    {
        $this->commentContent = $commentContent;

        return $this;
    }

    public function getCommentDate(): ?\DateTimeInterface
    {
        return $this->commentDate;
    }

    public function setCommentDate(\DateTimeInterface $commentDate): static
    {
        $this->commentDate = $commentDate;

        return $this;
    }

    public function getDate()
    {
        return $this->commentDate->format("d/m/Y à H:i");
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): static
    {
        $this->article = $article;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        // Condition ? valeur_si_vrai : valeur_si_faux;
        $this->username = $user ? $user->getUsername() : null;

        return $this;
    }

    public function __toString()
    {
        return $this -> commentContent;
    }
}

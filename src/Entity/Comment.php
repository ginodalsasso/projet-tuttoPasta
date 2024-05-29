<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentContent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $commentDate = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    private ?Article $article = null;

    public function __construct()
    {
        //initialise la date et l'heure du commentaire lors de la création de l'objet
        $timezone = new \DateTimeZone('Europe/Paris');
        $this->commentDate = new \DateTime('now', $timezone);
    }

    public function getId(): ?int
    {
        return $this->id;
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
}

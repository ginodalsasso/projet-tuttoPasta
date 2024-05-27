<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $articleTitle = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $articleContent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $articleDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $articleImage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticleTitle(): ?string
    {
        return $this->articleTitle;
    }

    public function setArticleTitle(string $articleTitle): static
    {
        $this->articleTitle = $articleTitle;

        return $this;
    }

    public function getArticleContent(): ?string
    {
        return $this->articleContent;
    }

    public function setArticleContent(string $articleContent): static
    {
        $this->articleContent = $articleContent;

        return $this;
    }

    public function getArticleDate(): ?\DateTimeInterface
    {
        return $this->articleDate;
    }

    public function setArticleDate(\DateTimeInterface $articleDate): static
    {
        $this->articleDate = $articleDate;

        return $this;
    }

    public function getArticleImage(): ?string
    {
        return $this->articleImage;
    }

    public function setArticleImage(?string $articleImage): static
    {
        $this->articleImage = $articleImage;

        return $this;
    }
}

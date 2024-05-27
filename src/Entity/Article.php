<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, mappedBy: 'articles')]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->addArticle($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->removeArticle($this);
        }

        return $this;
    }
}

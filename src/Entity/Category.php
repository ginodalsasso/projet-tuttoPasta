<?php

namespace App\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    // ---------------------------------ATTRIBUTS--------------------------------- //
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $categoryName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $categoryContent = null;

    /**
     * @var Collection<int, project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, inversedBy: 'categories')]
    private Collection $projects;

    /**
     * @var Collection<int, article>
     */
    #[ORM\ManyToMany(targetEntity: Article::class, inversedBy: 'categories')]
    private Collection $articles;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\OneToMany(targetEntity: Service::class, mappedBy: 'category')]
    private Collection $services;

    
    // ---------------------------------CONSTRUCT--------------------------------- //


    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->articles = new ArrayCollection();
        $this->services = new ArrayCollection();
    }


    // ---------------------------------GETTERS AND SETTERS--------------------------------- //

    // Slugifie mon titre pour une URL propre et pour le SEO
    public function  getSlug(): string
    {
        return (new Slugify())->slugify($this->categoryName);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function setCategoryName(string $categoryName): static
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    public function getCategoryContent(): ?string
    {
        return $this->categoryContent;
    }

    public function setCategoryContent(?string $categoryContent): static
    {
        $this->categoryContent = $categoryContent;

        return $this;
    }

    /**
     * @return Collection<int, project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
        }

        return $this;
    }

    public function removeProject(project $project): static
    {
        $this->projects->removeElement($project);

        return $this;
    }

    /**
     * @return Collection<int, article>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(article $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
        }

        return $this;
    }

    public function removeArticle(article $article): static
    {
        $this->articles->removeElement($article);

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setCategory($this);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getCategory() === $this) {
                $service->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this -> categoryName;
    }
}

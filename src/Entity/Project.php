<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $projectName = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $projectContent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $projectDate = null;

    /**
     * @var Collection<int, projectImg>
     */
    #[ORM\OneToMany(targetEntity: projectImg::class, mappedBy: 'project')]
    private Collection $images;

    /**
     * @var Collection<int, category>
     */
    #[ORM\ManyToMany(targetEntity: category::class, inversedBy: 'projects')]
    private Collection $project;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->project = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProjectName(): ?string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): static
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function getProjectContent(): ?string
    {
        return $this->projectContent;
    }

    public function setProjectContent(string $projectContent): static
    {
        $this->projectContent = $projectContent;

        return $this;
    }

    public function getProjectDate(): ?\DateTimeInterface
    {
        return $this->projectDate;
    }

    public function setProjectDate(\DateTimeInterface $projectDate): static
    {
        $this->projectDate = $projectDate;

        return $this;
    }

    /**
     * @return Collection<int, projectImg>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(projectImg $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setProject($this);
        }

        return $this;
    }

    public function removeImage(projectImg $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProject() === $this) {
                $image->setProject(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, category>
     */
    public function getProject(): Collection
    {
        return $this->project;
    }

    public function addProject(category $project): static
    {
        if (!$this->project->contains($project)) {
            $this->project->add($project);
        }

        return $this;
    }

    public function removeProject(category $project): static
    {
        $this->project->removeElement($project);

        return $this;
    }
}

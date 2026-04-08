<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GuideCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuideCategoryRepository::class)]
#[ORM\Table(name: 'guide_category')]
#[ORM\Index(columns: ['slug'], name: 'idx_guide_category_slug')]
#[ORM\Index(columns: ['position'], name: 'idx_guide_category_position')]
#[ORM\HasLifecycleCallbacks]
class GuideCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom affiché de la catégorie.
     * Exemple : "Classes", "Démoniste", "Affliction".
     */
    #[ORM\Column(length: 150)]
    private string $name = '';

    /**
     * Slug unique utilisé dans les URLs.
     * Exemple : "classes", "demoniste", "affliction".
     */
    #[ORM\Column(length: 180, unique: true)]
    private string $slug = '';

    /**
     * Petite description optionnelle.
     * Utile pour introduire une catégorie sur une page publique.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Ordre d'affichage dans les menus et les listes.
     * Plus la valeur est petite, plus la catégorie remonte.
     */
    #[ORM\Column]
    private int $position = 0;

    /**
     * Permet de masquer une catégorie sans la supprimer.
     */
    #[ORM\Column]
    private bool $isActive = true;

    /**
     * Icône éventuelle (nom de fichier, classe CSS, chemin...).
     * Facultatif pour l'instant, mais pratique à prévoir.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    /**
     * Parent de la catégorie pour construire l'arborescence.
     * Nullable car les catégories racines n'ont pas de parent.
     */
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(onDelete: 'SET NULL', nullable: true)]
    private ?self $parent = null;

    /**
     * Enfants de la catégorie.
     */
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    #[ORM\OrderBy(['position' => 'ASC', 'name' => 'ASC'])]
    private Collection $children;

    /**
     * Guides rattachés à cette catégorie.
     */
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Guide::class)]
    #[ORM\OrderBy(['title' => 'ASC'])]
    private Collection $guides;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->guides = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();

        $this->createdAt ??= $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = trim($name);

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = trim($slug);

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description !== null ? trim($description) : null;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon !== null ? trim($icon) : null;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        /**
         * Petite sécurité métier simple :
         * on évite qu'une catégorie soit son propre parent.
         */
        if ($parent === $this) {
            return $this;
        }

        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, GuideCategory>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Guide>
     */
    public function getGuides(): Collection
    {
        return $this->guides;
    }

    public function addGuide(Guide $guide): self
    {
        if (!$this->guides->contains($guide)) {
            $this->guides->add($guide);
            $guide->setCategory($this);
        }

        return $this;
    }

    public function removeGuide(Guide $guide): self
    {
        if ($this->guides->removeElement($guide)) {
            if ($guide->getCategory() === $this) {
                // On ne met pas null ici si la relation est obligatoire côté Guide.
            }
        }

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Méthode pratique pour savoir si la catégorie est une racine.
     */
    public function isRoot(): bool
    {
        return $this->parent === null;
    }

        /**
     * Retourne le chemin hiérarchique complet de la catégorie.
     *
     * Exemple :
     * Classes > Démoniste > Affliction
     */
    public function getBreadcrumbName(string $separator = ' > '): string
    {
        $parts = [];
        $current = $this;

        while ($current !== null) {
            array_unshift($parts, $current->getName());
            $current = $current->getParent();
        }

        return implode($separator, $parts);
    }
}
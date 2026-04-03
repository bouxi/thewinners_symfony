<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GuideRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuideRepository::class)]
#[ORM\Table(name: 'guide')]
#[ORM\Index(columns: ['slug'], name: 'idx_guide_slug')]
#[ORM\Index(columns: ['is_published'], name: 'idx_guide_is_published')]
#[ORM\Index(columns: ['published_at'], name: 'idx_guide_published_at')]
#[ORM\HasLifecycleCallbacks]
class Guide
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Titre principal du guide.
     */
    #[ORM\Column(length: 255)]
    private string $title = '';

    /**
     * Slug unique pour l'URL publique.
     */
    #[ORM\Column(length: 255, unique: true)]
    private string $slug = '';

    /**
     * Petit résumé affiché dans les listes ou cartes.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $excerpt = null;

    /**
     * Contenu complet du guide.
     * On utilise TEXT pour pouvoir stocker du contenu riche.
     */
    #[ORM\Column(type: 'text')]
    private string $content = '';

    /**
     * Image mise en avant éventuelle.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featuredImage = null;

    /**
     * Permet de distinguer un brouillon d'un guide public.
     */
    #[ORM\Column]
    private bool $isPublished = false;

    /**
     * Date de publication effective.
     * Nullable tant que le guide n'est pas publié.
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * Catégorie obligatoire :
     * un guide doit toujours appartenir à une catégorie.
     */
    #[ORM\ManyToOne(targetEntity: GuideCategory::class, inversedBy: 'guides')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?GuideCategory $category = null;

    /**
     * Auteur du guide.
     * Nullable pour plus de souplesse au début.
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $author = null;

    public function __toString(): string
    {
        return $this->title;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = trim($title);

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

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): self
    {
        $this->excerpt = $excerpt !== null ? trim($excerpt) : null;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = trim($content);

        return $this;
    }

    public function getFeaturedImage(): ?string
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?string $featuredImage): self
    {
        $this->featuredImage = $featuredImage !== null ? trim($featuredImage) : null;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        /**
         * Petite logique pratique :
         * si on publie un guide sans date, on pose la date automatiquement.
         */
        if ($isPublished && $this->publishedAt === null) {
            $this->publishedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

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

    public function getCategory(): ?GuideCategory
    {
        return $this->category;
    }

    public function setCategory(?GuideCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}
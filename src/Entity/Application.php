<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\ApplicationStatus;
use App\Repository\GuildApplicationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuildApplicationRepository::class)]
#[ORM\Table(name: 'guild_application')]
class Application
{
    /**
     * Constantes “string” pratiques si tu en as besoin côté Twig/JS,
     * MAIS ta source de vérité ici reste l’Enum ApplicationStatus.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * 1 candidature max par user => OneToOne + unique.
     */
    #[ORM\OneToOne(inversedBy: 'guildApplication')]
    #[ORM\JoinColumn(unique: true, nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 40)]
    private string $class = '';

    #[ORM\Column(length: 60)]
    private string $specialization = '';

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $playtime = null;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $availability = null;

    #[ORM\Column(type: 'text')]
    private string $motivation = '';

    /**
     * Statut via Enum (recommandé).
     * pending / accepted / rejected
     */
    #[ORM\Column(type: 'string', length: 20, enumType: ApplicationStatus::class)]
    private ApplicationStatus $status = ApplicationStatus::PENDING;

    /**
     * Date de soumission.
     */
    #[ORM\Column]
    private \DateTimeImmutable $submittedAt;

    /**
     * Date de review (accept/refuse/pending) par un admin (nullable).
     */
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    /**
     * Admin qui a traité la candidature (nullable).
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $reviewedBy = null;

    /**
     * Note interne admin (non visible candidat) (nullable).
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $adminNote = null;

    /**
     * Fil de messages lié à la candidature (admin ↔ candidat).
     * Affiché côté admin et côté candidat.
     *
     * orphanRemoval=true => supprimer la candidature supprime ses messages.
     */
    #[ORM\OneToMany(targetEntity: ApplicationMessage::class, mappedBy: 'application', orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $messages;

    public function __construct()
    {
        $this->submittedAt = new \DateTimeImmutable();
        $this->status = ApplicationStatus::PENDING;
        $this->messages = new ArrayCollection();
    }

    // -------------------------
    // Getters / Setters (commentés)
    // -------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Candidat (User) lié à cette candidature.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;
        return $this;
    }

    public function getSpecialization(): string
    {
        return $this->specialization;
    }

    public function setSpecialization(string $specialization): self
    {
        $this->specialization = $specialization;
        return $this;
    }

    public function getPlaytime(): ?string
    {
        return $this->playtime;
    }

    public function setPlaytime(?string $playtime): self
    {
        $this->playtime = $playtime;
        return $this;
    }

    public function getAvailability(): ?string
    {
        return $this->availability;
    }

    public function setAvailability(?string $availability): self
    {
        $this->availability = $availability;
        return $this;
    }

    public function getMotivation(): string
    {
        return $this->motivation;
    }

    public function setMotivation(string $motivation): self
    {
        $this->motivation = $motivation;
        return $this;
    }

    /**
     * Statut (Enum).
     */
    public function getStatus(): ApplicationStatus
    {
        return $this->status;
    }

    public function setStatus(ApplicationStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Helpers pratiques pour Twig / conditions.
     */
    public function isPending(): bool
    {
        return $this->status === ApplicationStatus::PENDING;
    }

    public function isAccepted(): bool
    {
        return $this->status === ApplicationStatus::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this->status === ApplicationStatus::REJECTED;
    }

    public function getSubmittedAt(): \DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(\DateTimeImmutable $submittedAt): self
    {
        $this->submittedAt = $submittedAt;
        return $this;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?\DateTimeImmutable $reviewedAt): self
    {
        $this->reviewedAt = $reviewedAt;
        return $this;
    }

    public function getReviewedBy(): ?User
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?User $reviewedBy): self
    {
        $this->reviewedBy = $reviewedBy;
        return $this;
    }

    public function getAdminNote(): ?string
    {
        return $this->adminNote;
    }

    public function setAdminNote(?string $adminNote): self
    {
        $this->adminNote = $adminNote;
        return $this;
    }

    /**
     * @return Collection<int, ApplicationMessage>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * Ajoute un message au fil.
     * Important : on synchronise le “côté owning” (ApplicationMessage::application).
     */
    public function addMessage(ApplicationMessage $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setApplication($this);
        }

        return $this;
    }

    public function removeMessage(ApplicationMessage $message): self
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getApplication() === $this) {
                $message->setApplication(null);
            }
        }

        return $this;
    }
}

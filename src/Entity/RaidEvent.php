<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RaidEventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RaidEventRepository::class)]
#[ORM\Table(name: 'raid_event')]
#[ORM\Index(columns: ['starts_at'], name: 'idx_raid_starts_at')]
class RaidEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Titre du raid (ex: ICC 25, Naxx, Ulduar…)
     */
    #[ORM\Column(length: 120)]
    private string $title = '';

    /**
     * Début du raid.
     */
    #[ORM\Column(name: 'starts_at')]
    private \DateTimeImmutable $startsAt;

    /**
     * Fin du raid.
     */
    #[ORM\Column(name: 'ends_at')]
    private \DateTimeImmutable $endsAt;

    /**
     * Description / consignes (optionnel).
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Créateur du raid (tout ROLE_USER peut créer).
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column(name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    #[ORM\OneToMany(mappedBy: 'raid', targetEntity: RaidSignup::class, orphanRemoval: true)]
    private Collection $signups;

    public function __construct()
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;

        // Valeurs par défaut : “maintenant” + 2h (tu peux adapter)
        $this->startsAt = $now->modify('+1 day')->setTime(20, 0);
        $this->endsAt = $now->modify('+1 day')->setTime(23, 0);

        $this->signups = new ArrayCollection();
    }

    // -------------------------
    // Getters / setters
    // -------------------------

    public function getId(): ?int { return $this->id; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }

    public function getStartsAt(): \DateTimeImmutable { return $this->startsAt; }
    public function setStartsAt(\DateTimeImmutable $startsAt): self { $this->startsAt = $startsAt; return $this; }

    public function getEndsAt(): \DateTimeImmutable { return $this->endsAt; }
    public function setEndsAt(\DateTimeImmutable $endsAt): self { $this->endsAt = $endsAt; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getCreatedBy(): ?User { return $this->createdBy; }
    public function setCreatedBy(User $createdBy): self { $this->createdBy = $createdBy; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    /** @return Collection<int, RaidSignup> */
    public function getSignups(): Collection { return $this->signups; }
}

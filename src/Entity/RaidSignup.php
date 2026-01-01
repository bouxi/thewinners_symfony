<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RaidSignupRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\RaidRole;

#[ORM\Entity(repositoryClass: RaidSignupRepository::class)]
#[ORM\Table(name: 'raid_signup')]
#[ORM\UniqueConstraint(name: 'uniq_raid_user', columns: ['raid_id', 'user_id'])]
class RaidSignup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Raid concerné.
     */
    #[ORM\ManyToOne(inversedBy: 'signups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?RaidEvent $raid = null;

    /**
     * Utilisateur inscrit.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * Rôle prévu (tank/heal/dps) – simple string (facile à évoluer en enum après).
     */
    #[ORM\Column(type: 'string', enumType: RaidRole::class, length: 10)]
    private RaidRole $role = RaidRole::DPS;

    /**
     * Note optionnelle (ex: “je viens en reroll”, “je peux switch heal”)
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column(name: 'created_at')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters / setters
    public function getId(): ?int { return $this->id; }

    public function getRaid(): ?RaidEvent { return $this->raid; }
    public function setRaid(RaidEvent $raid): self { $this->raid = $raid; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

    public function getRole(): RaidRole
    {
        return $this->role;
    }

    public function setRole(RaidRole $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): self { $this->note = $note; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

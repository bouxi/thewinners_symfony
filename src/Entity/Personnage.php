<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CombatRole;
use App\Repository\PersonnageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonnageRepository::class)]
class Personnage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ✅ Propriétaire du perso
    #[ORM\ManyToOne(inversedBy: 'personnages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    private string $name = '';

    #[ORM\Column(length: 50)]
    private string $class = '';

    #[ORM\Column(length: 80)]
    private string $spec = '';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $race = null;

    // Métiers (simple version) : 2 champs
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $profession1 = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $profession2 = null;

    #[ORM\Column(enumType: CombatRole::class, nullable: true)]
    private ?CombatRole $combatRole = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isMain = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $isPublic = true;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

    public function getName(): string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getClass(): string { return $this->class; }
    public function setClass(string $class): self { $this->class = $class; return $this; }

    public function getSpec(): string { return $this->spec; }
    public function setSpec(string $spec): self { $this->spec = $spec; return $this; }

    public function getRace(): ?string { return $this->race; }
    public function setRace(?string $race): self { $this->race = $race; return $this; }

    public function getProfession1(): ?string { return $this->profession1; }
    public function setProfession1(?string $profession1): self { $this->profession1 = $profession1; return $this; }

    public function getProfession2(): ?string { return $this->profession2; }
    public function setProfession2(?string $profession2): self { $this->profession2 = $profession2; return $this; }

    public function getCombatRole(): ?CombatRole { return $this->combatRole; }
    public function setCombatRole(?CombatRole $combatRole): self { $this->combatRole = $combatRole; return $this; }

    public function isMain(): bool { return $this->isMain; }
    public function setIsMain(bool $isMain): self { $this->isMain = $isMain; return $this; }

    public function isPublic(): bool { return $this->isPublic; }
    public function setIsPublic(bool $isPublic): self { $this->isPublic = $isPublic; return $this; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
}

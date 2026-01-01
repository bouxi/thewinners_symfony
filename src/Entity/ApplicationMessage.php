<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ApplicationMessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApplicationMessageRepository::class)]
#[ORM\Table(name: 'application_message')]
class ApplicationMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Candidature associée à ce message.
     */
    #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Application $application = null;

    /**
     * Expéditeur (admin ou candidat).
     */
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $sender = null;

    #[ORM\Column(type: 'text')]
    private string $content = '';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * Permet d’afficher un badge côté candidat si nouveaux messages.
     * Ici c’est “lu par le candidat”.
     */
    #[ORM\Column]
    private bool $isReadByApplicant = false;

    public function __construct()
    {
        // Par défaut, date de création au moment de l’instanciation
        $this->createdAt = new \DateTimeImmutable();
    }

    // -------------------------
    // Getters / Setters
    // -------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApplication(): ?Application
    {
        return $this->application;
    }

    public function setApplication(?Application $application): self
    {
        $this->application = $application;
        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(User $sender): self
    {
        $this->sender = $sender;
        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function isReadByApplicant(): bool
    {
        return $this->isReadByApplicant;
    }

    public function setIsReadByApplicant(bool $isRead): self
    {
        $this->isReadByApplicant = $isRead;
        return $this;
    }
}

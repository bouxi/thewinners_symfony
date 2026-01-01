<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'message')]
#[ORM\Index(columns: ['recipient_read_at'], name: 'idx_message_read')]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $conversation = null;

    /**
     * Expéditeur
     * ✅ Doit matcher User::$sentMessages (mappedBy: sender)
     */
    #[ORM\ManyToOne(inversedBy: 'sentMessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sender = null;

    #[ORM\Column(type: 'text')]
    private string $content = '';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * Date à laquelle le destinataire a lu ce message (null = non lu)
     */
    #[ORM\Column(name: 'recipient_read_at', nullable: true)]
    private ?\DateTimeImmutable $recipientReadAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function markAsRead(): void
    {
        if ($this->recipientReadAt === null) {
            $this->recipientReadAt = new \DateTimeImmutable();
        }
    }

    // -------------------------
    // Getters / setters
    // -------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConversation(): ?Conversation
    {
        return $this->conversation;
    }

    public function setConversation(Conversation $conversation): self
    {
        $this->conversation = $conversation;

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

    public function getRecipientReadAt(): ?\DateTimeImmutable
    {
        return $this->recipientReadAt;
    }

    public function setRecipientReadAt(?\DateTimeImmutable $recipientReadAt): self
    {
        $this->recipientReadAt = $recipientReadAt;

        return $this;
    }
}

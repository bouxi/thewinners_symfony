<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ConversationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\Table(name: 'conversation')]
#[ORM\UniqueConstraint(name: 'uniq_conversation_pair', columns: ['user_one_id', 'user_two_id'])]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Participant 1 (toujours le plus petit ID, par convention)
     * ✅ name: user_one_id => cohérent avec la contrainte unique
     */
    #[ORM\ManyToOne(inversedBy: 'conversationsAsUserOne')]
    #[ORM\JoinColumn(name: 'user_one_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $userOne = null;

    /**
     * Participant 2
     * ✅ name: user_two_id => cohérent avec la contrainte unique
     */
    #[ORM\ManyToOne(inversedBy: 'conversationsAsUserTwo')]
    #[ORM\JoinColumn(name: 'user_two_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $userTwo = null;

    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: Message::class, orphanRemoval: true)]
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    private Collection $messages;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return 'Conversation #'.($this->id ?? 'new');
    }

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // -------------------------
    // Getters / setters
    // -------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserOne(): ?User
    {
        return $this->userOne;
    }

    public function setUserOne(User $user): self
    {
        $this->userOne = $user;

        return $this;
    }

    public function getUserTwo(): ?User
    {
        return $this->userTwo;
    }

    public function setUserTwo(User $user): self
    {
        $this->userTwo = $user;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * Helpers clean pour la relation (bonne pratique)
     */
    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
            $this->touch();
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // orphanRemoval=true => Doctrine supprimera le message
            $this->touch();
        }

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Retourne l'autre participant (pratique pour l'affichage).
     */
    public function getOtherUser(User $me): ?User
    {
        if ($this->userOne && $this->userOne->getId() === $me->getId()) {
            return $this->userTwo;
        }

        if ($this->userTwo && $this->userTwo->getId() === $me->getId()) {
            return $this->userOne;
        }

        return null;
    }

    /**
     * Vérifie si l’utilisateur fait partie de la conversation.
     */
    public function hasUser(User $user): bool
    {
        return ($this->userOne && $this->userOne->getId() === $user->getId())
            || ($this->userTwo && $this->userTwo->getId() === $user->getId());
    }
}

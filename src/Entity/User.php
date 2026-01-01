<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\GuildRank;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Email utilisé comme identifiant de connexion.
     */
    #[ORM\Column(length: 180, unique: true)]
    private string $email = '';

    /**
     * Rôles “bruts” stockés en base (JSON).
     * getRoles() ajoutera automatiquement ceux liés au grade + ROLE_USER.
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * Mot de passe hashé (bcrypt/argon2i/argon2id).
     */
    #[ORM\Column]
    private string $password = '';

    /**
     * Pseudo affiché sur le site (nom de perso, pseudo de guilde, etc.).
     */
    #[ORM\Column(length: 50, unique: true)]
    private string $pseudo = '';

    /**
     * Conversations où l'utilisateur est userOne.
     */
    #[ORM\OneToMany(targetEntity: Conversation::class, mappedBy: 'userOne', orphanRemoval: true)]
    private Collection $conversationsAsUserOne;

    /**
     * Conversations où l'utilisateur est userTwo.
     */
    #[ORM\OneToMany(targetEntity: Conversation::class, mappedBy: 'userTwo', orphanRemoval: true)]
    private Collection $conversationsAsUserTwo;

    /**
     * Messages envoyés par l'utilisateur (historique d'envoi).
     *
     * ✅ IMPORTANT :
     * - côté Message::$sender, il faut bien : #[ORM\ManyToOne(inversedBy: 'sentMessages')]
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender', orphanRemoval: true)]
    private Collection $sentMessages;

    /**
     * Candidature de guilde (1 seule par user).
     */
    #[ORM\OneToOne(targetEntity: Application::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Application $guildApplication = null;

    /**
     * Grade dans la guilde, typé avec notre enum.
     * Stockage en string en base, mais manipulé en GuildRank dans le code.
     *
     * ✅ Version la plus "clean" : enumType suffit.
     * (Le "type: string" est optionnel, et parfois l’IDE le souligne pour rien)
     */
    #[ORM\Column(length: 20, enumType: GuildRank::class)]
    private GuildRank $guildRank = GuildRank::VISITOR;

    /**
     * Date d'inscription sur le site.
     */
    #[ORM\Column]
    private \DateTimeImmutable $dateInscription;

    /**
     * Chemin relatif du fichier avatar (ex: uploads/avatars/xxx.png).
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = 'images/default-avatar.png';

    /**
     * Flag "email vérifié" si tu mets un système de confirmation.
     */
    #[ORM\Column(options: ['default' => false])]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->dateInscription = new \DateTimeImmutable();
        $this->guildRank = GuildRank::VISITOR;

        $this->conversationsAsUserOne = new ArrayCollection();
        $this->conversationsAsUserTwo = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
    }

    // -------------------------
    // Getters / setters de base
    // -------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = strtolower($email);

        return $this;
    }

    /**
     * Symfony utilise cette méthode comme identifiant unique.
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getGuildRank(): GuildRank
    {
        return $this->guildRank;
    }

    public function setGuildRank(GuildRank $guildRank): self
    {
        $this->guildRank = $guildRank;

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversationsAsUserOne(): Collection
    {
        return $this->conversationsAsUserOne;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversationsAsUserTwo(): Collection
    {
        return $this->conversationsAsUserTwo;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    /**
     * Helpers "clean" (optionnels mais pro) pour garder la relation cohérente.
     * Tu peux les utiliser plus tard si besoin.
     */
    public function addSentMessage(Message $message): self
    {
        if (!$this->sentMessages->contains($message)) {
            $this->sentMessages->add($message);
            $message->setSender($this);
        }

        return $this;
    }

    public function removeSentMessage(Message $message): self
    {
        if ($this->sentMessages->removeElement($message)) {
            if ($message->getSender() === $this) {
                // Important : ne casse pas ton mapping si tu veux garder l’historique
                // Ici on remet à null uniquement si ton JoinColumn est nullable.
                // Or chez toi il est nullable: false => donc on ne fait rien.
            }
        }

        return $this;
    }

    public function getGuildApplication(): ?Application
    {
        return $this->guildApplication;
    }

    public function setGuildApplication(?Application $application): self
    {
        $this->guildApplication = $application;

        return $this;
    }

    public function getDateInscription(): \DateTimeImmutable
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeImmutable $dateInscription): self
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar ?: 'images/default-avatar.png';
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    // -------------------------
    // Sécurité Symfony
    // -------------------------

    /**
     * Retourne les rôles de l'utilisateur.
     * On :
     *  - fusionne les rôles stockés en base ($this->roles)
     *  - (optionnel) tu peux ajouter ceux liés au grade si tu as une méthode GuildRank::baseRoles()
     *  - garantit toujours ROLE_USER
     */
    public function getRoles(): array
    {
        // Rôles custom stockés en base (ex: ROLE_ADMIN)
        $roles = $this->roles;

        // Toujours ROLE_USER
        $roles[] = 'ROLE_USER';

        // ✅ Ajoute le rôle lié au grade de guilde si nécessaire
        $guildRole = $this->guildRank->role();
        if ($guildRole !== null) {
            $roles[] = $guildRole;
        }

        return array_values(array_unique($roles));
    }


    public function setRoles(array $roles): self
    {
        // On ne met ici que les rôles “custom” éventuels.
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $hashedPassword): self
    {
        $this->password = $hashedPassword;

        return $this;
    }

    /**
     * Méthode prévue pour effacer des données sensibles temporaires.
     */
    public function eraseCredentials(): void
    {
        // Ex: $this->plainPassword = null;
    }

    public function getStoredRoles(): array
    {
        return $this->roles;
    }

}

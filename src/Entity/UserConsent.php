<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserConsentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserConsentRepository::class)]
class UserConsent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'userConsent')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $privacyAccepted = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $termsAccepted = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $cookiesAccepted = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $termsAcceptedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $privacyAcceptedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cookiesAcceptedAt = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $cookieChoice = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $privacyVersion = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $termsVersion = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $cookiesVersion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isPrivacyAccepted(): bool
    {
        return $this->privacyAccepted;
    }

    public function setPrivacyAccepted(bool $privacyAccepted): self
    {
        $this->privacyAccepted = $privacyAccepted;

        return $this;
    }

    public function isTermsAccepted(): bool
    {
        return $this->termsAccepted;
    }

    public function setTermsAccepted(bool $termsAccepted): self
    {
        $this->termsAccepted = $termsAccepted;

        return $this;
    }

    public function isCookiesAccepted(): bool
    {
        return $this->cookiesAccepted;
    }

    public function setCookiesAccepted(bool $cookiesAccepted): self
    {
        $this->cookiesAccepted = $cookiesAccepted;

        return $this;
    }


    public function getCookieChoice(): ?string
    {
        return $this->cookieChoice;
    }

    public function setCookieChoice(?string $cookieChoice): self
    {
        $this->cookieChoice = $cookieChoice;

        return $this;
    }

    public function getPrivacyVersion(): ?string
    {
        return $this->privacyVersion;
    }

    public function setPrivacyVersion(?string $privacyVersion): self
    {
        $this->privacyVersion = $privacyVersion;

        return $this;
    }

    public function getTermsVersion(): ?string
    {
        return $this->termsVersion;
    }

    public function setTermsVersion(?string $termsVersion): self
    {
        $this->termsVersion = $termsVersion;

        return $this;
    }

    public function getCookiesVersion(): ?string
    {
        return $this->cookiesVersion;
    }

    public function setCookiesVersion(?string $cookiesVersion): self
    {
        $this->cookiesVersion = $cookiesVersion;

        return $this;
    }

    public function getTermsAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->termsAcceptedAt;
    }

    public function setTermsAcceptedAt(?\DateTimeImmutable $termsAcceptedAt): self
    {
        $this->termsAcceptedAt = $termsAcceptedAt;

        return $this;
    }

    public function getPrivacyAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->privacyAcceptedAt;
    }

    public function setPrivacyAcceptedAt(?\DateTimeImmutable $privacyAcceptedAt): self
    {
        $this->privacyAcceptedAt = $privacyAcceptedAt;

        return $this;
    }

    public function getCookiesAcceptedAt(): ?\DateTimeImmutable
    {
        return $this->cookiesAcceptedAt;
    }

    public function setCookiesAcceptedAt(?\DateTimeImmutable $cookiesAcceptedAt): self
    {
        $this->cookiesAcceptedAt = $cookiesAcceptedAt;

        return $this;
    }
}
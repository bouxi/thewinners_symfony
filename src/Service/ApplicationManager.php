<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Application;
use App\Entity\ApplicationMessage;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class ApplicationManager
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * Change le statut + renseigne reviewedAt/reviewedBy + envoie un message automatique au candidat.
     *
     * $customMessage (optionnel) : si l’admin veut ajouter un complément.
     */
    public function setStatus(Application $application, string $status, User $admin, ?string $customMessage = null): void
    {
        if (!in_array($status, [Application::STATUS_PENDING, Application::STATUS_ACCEPTED, Application::STATUS_REJECTED], true)) {
            throw new \InvalidArgumentException('Statut invalide.');
        }

        $application->setStatus($status);
        $application->setReviewedAt(new \DateTimeImmutable());
        $application->setReviewedBy($admin);

        // Message automatique (notification)
        $content = match ($status) {
            Application::STATUS_ACCEPTED => "✅ Ta candidature a été **acceptée**. Bienvenue dans la guilde !",
            Application::STATUS_REJECTED => "❌ Ta candidature a été **refusée**. Merci pour ton intérêt.",
            default => "⏳ Ta candidature est repassée **en attente**. Nous revenons vers toi bientôt.",
        };

        if ($customMessage !== null && trim($customMessage) !== '') {
            $content .= "\n\nMessage de l’admin :\n" . trim($customMessage);
        }

        $this->createMessage($application, $admin, $content, false);
        $this->em->flush();
    }

    /**
     * L’admin pose une question au candidat (sans changer le statut).
     */
    public function askQuestion(Application $application, User $admin, string $content): void
    {
        $content = trim($content);
        if ($content === '') {
            throw new \InvalidArgumentException('Le message ne peut pas être vide.');
        }

        $this->createMessage($application, $admin, "❓ Question de l’admin :\n" . $content, false);
        $this->em->flush();
    }

    /**
     * Le candidat répond dans le fil.
     */
    public function applicantReply(Application $application, User $applicant, string $content): void
    {
        $content = trim($content);
        if ($content === '') {
            throw new \InvalidArgumentException('Le message ne peut pas être vide.');
        }

        // Ici, le message est déjà "lu" côté candidat (c’est lui qui écrit),
        // donc on met true directement.
        $this->createMessage($application, $applicant, $content, true);
        $this->em->flush();
    }

    private function createMessage(Application $application, User $sender, string $content, bool $readByApplicant): void
    {
        $msg = new ApplicationMessage();
        $msg->setApplication($application);
        $msg->setSender($sender);
        $msg->setContent($content);
        $msg->setCreatedAt(new \DateTimeImmutable());
        $msg->setIsReadByApplicant($readByApplicant);

        $this->em->persist($msg);
    }
}

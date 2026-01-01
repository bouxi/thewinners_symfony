<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

final class MessageManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private ConversationRepository $conversationRepository,
    ) {}

    /**
     * Démarre une conversation (si besoin) + envoie un message via ID.
     */
    public function startConversationAndSend(User $sender, int $recipientId, string $content): Conversation
    {
        $recipient = $this->userRepository->find($recipientId);
        if (!$recipient instanceof User) {
            throw new \RuntimeException('Destinataire introuvable.');
        }

        return $this->startConversationAndSendToUser($sender, $recipient, $content);
    }

    /**
     * Démarre une conversation (si besoin) + envoie un message via pseudo.
     * Bonus : suggestions si pseudo introuvable.
     */
    public function startConversationAndSendByPseudo(User $sender, string $recipientPseudo, string $content): Conversation
    {
        $recipientPseudo = trim($recipientPseudo);

        if ($recipientPseudo === '') {
            throw new \RuntimeException('Pseudo destinataire requis.');
        }

        // Recherche par pseudo (case-insensitive)
        $recipient = $this->userRepository->createQueryBuilder('u')
            ->where('LOWER(u.pseudo) = :p')
            ->setParameter('p', mb_strtolower($recipientPseudo))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$recipient instanceof User) {
            // Bonus : suggestions proches
            $suggestions = $this->userRepository->createQueryBuilder('u')
                ->select('u.pseudo')
                ->where('LOWER(u.pseudo) LIKE :q')
                ->setParameter('q', '%'.mb_strtolower($recipientPseudo).'%')
                ->setMaxResults(5)
                ->getQuery()
                ->getSingleColumnResult();

            if (count($suggestions) > 0) {
                throw new \RuntimeException('Pseudo introuvable. Suggestions : '.implode(', ', $suggestions));
            }

            throw new \RuntimeException('Aucun utilisateur trouvé avec ce pseudo.');
        }

        return $this->startConversationAndSendToUser($sender, $recipient, $content);
    }

    /**
     * Envoie un message dans une conversation existante (sécurisé : la conversation doit appartenir à l’utilisateur).
     */
    public function sendMessage(User $sender, int $conversationId, string $content): void
    {
        $content = trim($content);
        if ($content === '') {
            throw new \RuntimeException('Message vide.');
        }

        $conversation = $this->conversationRepository->findForUser($conversationId, $sender);
        if (!$conversation) {
            throw new \RuntimeException('Conversation introuvable ou accès interdit.');
        }

        $this->persistMessage($conversation, $sender, $content);
        $this->em->flush();
    }

    /**
     * Marque tous les messages reçus par $me comme lus (dans cette conversation).
     */
    public function markConversationAsRead(Conversation $conversation, User $me): void
    {
        $this->conversationRepository->markAsRead($conversation, $me);
    }

    /**
     * Logique centrale commune : conversation entre 2 users + envoi.
     */
    private function startConversationAndSendToUser(User $sender, User $recipient, string $content): Conversation
    {
        $content = trim($content);
        if ($content === '') {
            throw new \RuntimeException('Message vide.');
        }

        if ($recipient->getId() === $sender->getId()) {
            throw new \RuntimeException('Tu ne peux pas t’envoyer un message à toi-même.');
        }

        $conversation = $this->conversationRepository->findBetweenUsers($sender, $recipient);

        if (!$conversation) {
            // Convention : plus petit ID = userOne (évite les doublons)
            if ($sender->getId() < $recipient->getId()) {
                $conversation = (new Conversation())->setUserOne($sender)->setUserTwo($recipient);
            } else {
                $conversation = (new Conversation())->setUserOne($recipient)->setUserTwo($sender);
            }
            $this->em->persist($conversation);
        }

        $this->persistMessage($conversation, $sender, $content);
        $conversation->touch();

        $this->em->flush();

        return $conversation;
    }

    /**
     * Persist un message.
     */
    private function persistMessage(Conversation $conversation, User $sender, string $content): void
    {
        $message = (new Message())
            ->setConversation($conversation)
            ->setSender($sender)
            ->setContent($content);

        $this->em->persist($message);
    }
}

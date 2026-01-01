<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    /**
     * Conversations de l’utilisateur + (dernier message + nb non-lus) calculés.
     *
     * Retour : array de "rows" => [
     *   'conversation' => Conversation,
     *   'lastMessageAt' => ?DateTimeImmutable,
     *   'lastMessagePreview' => ?string,
     *   'unreadCount' => int
     * ]
     */
    public function findForUserWithMeta(User $me): array
    {
        // On récupère les conversations de $me
        $qb = $this->createQueryBuilder('c')
            ->leftJoin('c.messages', 'm')
            ->addSelect('MAX(m.createdAt) AS lastMessageAt')
            ->addSelect("SUBSTRING(MAX(CONCAT(m.createdAt, '||', m.content)), 22) AS lastMessagePreview")
            ->addSelect("SUM(CASE WHEN m.recipientReadAt IS NULL AND m.sender != :me THEN 1 ELSE 0 END) AS unreadCount")
            ->where('c.userOne = :me OR c.userTwo = :me')
            ->setParameter('me', $me)
            ->groupBy('c.id')
            ->orderBy('lastMessageAt', 'DESC');

        // NB: le hack CONCAT+MAX permet d’avoir le contenu “du dernier message”.
        // C’est simple et ça évite d’ajouter des colonnes en BDD.

        $rows = $qb->getQuery()->getResult();

        // Doctrine renvoie des tableaux mixtes, on normalise
        $out = [];
        foreach ($rows as $row) {
            /** @var Conversation $conv */
            $conv = $row[0];

            $out[] = [
                'conversation' => $conv,
                'lastMessageAt' => $row['lastMessageAt'] ?? null,
                'lastMessagePreview' => $row['lastMessagePreview'] ?? null,
                'unreadCount' => (int) ($row['unreadCount'] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * Sécurise l'accès : retourne la conversation si elle appartient à $me.
     */
    public function findForUser(int $conversationId, User $me): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->andWhere('c.userOne = :me OR c.userTwo = :me')
            ->setParameter('id', $conversationId)
            ->setParameter('me', $me)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Trouve une conversation entre 2 users (ordre indifférent).
     */
    public function findBetweenUsers(User $a, User $b): ?Conversation
    {
        return $this->createQueryBuilder('c')
            ->where('(c.userOne = :a AND c.userTwo = :b) OR (c.userOne = :b AND c.userTwo = :a)')
            ->setParameter('a', $a)
            ->setParameter('b', $b)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Marque comme lus tous les messages reçus par $me dans cette conversation.
     */
    public function markAsRead(Conversation $conversation, User $me): int
    {
        // Update DQL (rapide) : messages non lus où sender != me
        return $this->getEntityManager()->createQuery(
            'UPDATE App\Entity\Message m
             SET m.recipientReadAt = :now
             WHERE m.conversation = :c
               AND m.recipientReadAt IS NULL
               AND m.sender != :me'
        )
            ->setParameter('now', new \DateTimeImmutable())
            ->setParameter('c', $conversation)
            ->setParameter('me', $me)
            ->execute();
    }
}

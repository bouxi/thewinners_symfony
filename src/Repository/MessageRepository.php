<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

final class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    /**
     * Compte les messages non lus pour un utilisateur.
     * Ici : non lu = recipientReadAt IS NULL ET sender != user
     */
    public function countUnreadForUser(User $user): int
    {
        return (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->innerJoin('m.conversation', 'c')
            ->where('m.recipientReadAt IS NULL')
            ->andWhere('m.sender != :me')
            ->andWhere('(c.userOne = :me OR c.userTwo = :me)')
            ->setParameter('me', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Pagination des messages (du plus ancien au plus récent) sur une conversation.
     *
     * @return array{items: Message[], total: int, pages: int}
     */
    public function paginateConversation(Conversation $conversation, int $page, int $limit): array
    {
        $page = max(1, $page);
        $limit = max(1, min(100, $limit));
        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('m')
            ->where('m.conversation = :c')
            ->setParameter('c', $conversation)
            ->orderBy('m.createdAt', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $paginator = new Paginator($qb);
        $total = count($paginator);
        $pages = (int) ceil($total / $limit);

        return [
            'items' => iterator_to_array($paginator),
            'total' => $total,
            'pages' => max(1, $pages),
        ];
    }
}

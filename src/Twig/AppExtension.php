<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\User;
use App\Repository\MessageRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class AppExtension extends AbstractExtension
{
    public function __construct(private MessageRepository $messageRepository) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_messages_count', [$this, 'unreadMessagesCount']),
        ];
    }

    public function unreadMessagesCount(?User $user): int
    {
        return $user ? $this->messageRepository->countUnreadForUser($user) : 0;
    }
}

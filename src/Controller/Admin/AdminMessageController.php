<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/messages')]
final class AdminMessageController extends AbstractController
{
    #[Route('/conversations', name: 'admin_messages_conversations', methods: ['GET'])]
    public function conversations(Request $request, ConversationRepository $repo): Response
    {
        // Protection (en plus de access_control)
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $q = trim((string) $request->query->get('q', ''));

        $qb = $repo->createQueryBuilder('c')
            ->join('c.userOne', 'u1')
            ->join('c.userTwo', 'u2')
            ->addSelect('u1', 'u2')
            ->orderBy('c.updatedAt', 'DESC');

        if ($q !== '') {
            $qb->andWhere('u1.email LIKE :q OR u2.email LIKE :q OR u1.pseudo LIKE :q OR u2.pseudo LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        $conversations = $qb->getQuery()->getResult();

        return $this->render('admin/messages/conversations.html.twig', [
            'conversations' => $conversations,
            'q' => $q,
        ]);
    }

    #[Route('/conversations/{id}', name: 'admin_messages_conversation_show', methods: ['GET'])]
    public function show(Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // IMPORTANT : si tu veux éviter du lazy-loading côté twig,
        // on peut aussi charger les messages via un repo + join fetch.
        return $this->render('admin/messages/conversation_show.html.twig', [
            'conversation' => $conversation,
        ]);
    }

    #[Route('/conversations/{id}/delete', name: 'admin_messages_conversation_delete', methods: ['POST'])]
    public function deleteConversation(
        Conversation $conversation,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (
            !$this->isCsrfTokenValid(
                'delete_conversation_'.$conversation->getId(),
                (string) $request->request->get('_token')
            )
        ) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_messages_conversations');
        }

        $em->remove($conversation);
        $em->flush();

        $this->addFlash('success', 'Conversation supprimée ✅');
        return $this->redirectToRoute('admin_messages_conversations');
    }
}

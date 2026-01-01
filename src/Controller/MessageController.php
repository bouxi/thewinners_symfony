<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Service\MessageManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

#[Route('/messages')]
final class MessageController extends AbstractController
{
    #[Route('', name: 'app_messages', methods: ['GET'])]
    public function index(
        Request $request,
        ConversationRepository $conversationRepository,
        MessageRepository $messageRepository,
        MessageManager $messageManager,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $me = $this->getUser();
        \assert($me instanceof User);

        // Liste conversations (rows : conversation + meta)
        $conversations = $conversationRepository->findForUserWithMeta($me);

        // Conversation sélectionnée
        $conversationId = $request->query->getInt('c', 0);
        $activeConversation = null;

        if ($conversationId > 0) {
            $activeConversation = $conversationRepository->findForUser($conversationId, $me);
        }

        // Si aucune sélection et qu'on a des conversations -> ouvrir la 1ère conversation
        if (!$activeConversation && count($conversations) > 0) {
            /** @var Conversation $firstConversation */
            $firstConversation = $conversations[0]['conversation'];
            $activeConversation = $firstConversation;
        }

        $page = max(1, $request->query->getInt('page', 1));
        $limit = 20;

        // Structure identique même si pas de conversation (évite les erreurs Twig)
        $messages = [
            'items' => [],
            'total' => 0,
            'pages' => 1,
        ];

        if ($activeConversation) {
            // Marquer comme lus à l’ouverture
            $messageManager->markConversationAsRead($activeConversation, $me);

            // Pagination
            $messages = $messageRepository->paginateConversation($activeConversation, $page, $limit);
        }

        // Liste des utilisateurs “guild” pour la datalist (à ajuster selon tes règles)
        $guildUsers = $userRepository->createQueryBuilder('u')
            ->where('u.pseudo != :mePseudo')
            ->setParameter('mePseudo', $me->getPseudo())
            ->orderBy('u.pseudo', 'ASC')
            ->getQuery()
            ->getResult();


        return $this->render('messages/index.html.twig', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
            'page' => $page,
            'limit' => $limit,
            'guildUsers' => $guildUsers,
        ]);
    }

    #[Route('/new', name: 'app_messages_new', methods: ['POST'])]
    public function new(
        Request $request,
        MessageManager $messageManager
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $me = $this->getUser();
        \assert($me instanceof User);

        if (!$this->isCsrfTokenValid('message_new', (string)$request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_messages');
        }

        $recipientPseudo = trim((string)$request->request->get('recipient_pseudo', ''));
        $content = trim((string)$request->request->get('content', ''));

        if ($recipientPseudo === '' || $content === '') {
            $this->addFlash('danger', 'Destinataire et message requis.');
            return $this->redirectToRoute('app_messages');
        }

        try {
            $conversation = $messageManager->startConversationAndSendByPseudo($me, $recipientPseudo, $content);
            return $this->redirectToRoute('app_messages', ['c' => $conversation->getId()]);
        } catch (\RuntimeException $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('app_messages');
        }
    }



    #[Route('/send', name: 'app_messages_send', methods: ['POST'])]
    public function send(
        Request $request,
        MessageManager $messageManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $me = $this->getUser();
        \assert($me instanceof User);

        $conversationId = (int) $request->request->get('conversation_id', 0);
        $content = trim((string) $request->request->get('content', ''));

        if (!$this->isCsrfTokenValid('message_send_'.$conversationId, (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_messages', ['c' => $conversationId ?: null]);
        }

        if ($conversationId <= 0 || $content === '') {
            return $this->redirectToRoute('app_messages', ['c' => $conversationId ?: null]);
        }

        try {
            $messageManager->sendMessage($me, $conversationId, $content);
        } catch (\RuntimeException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('app_messages', ['c' => $conversationId]);
    }
}

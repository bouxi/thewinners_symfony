<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\GuildMemberType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/members')]
final class AdminGuildMemberController extends AbstractController
{
    #[Route('', name: 'admin_members_index', methods: ['GET'])]
    public function index(UserRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Liste de tous les users, tri simple (tu pourras filtrer après)
        $users = $repo->findBy([], ['pseudo' => 'ASC']);

        return $this->render('admin/members/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_members_edit', methods: ['GET', 'POST'])]
    public function edit(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(GuildMemberType::class, $user, [
            'csrf_token_id' => 'admin_member_edit_'.$user->getId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Membre mis à jour ✅');
            return $this->redirectToRoute('admin_members_index');
        }

        return $this->render('admin/members/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}

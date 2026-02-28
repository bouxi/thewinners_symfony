<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\UserGuildRankType;
use App\Repository\UserRepository;
use App\Security\Voter\UserRoleVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/users')]
final class AdminUserController extends AbstractController
{
    #[Route('', name: 'admin_users_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Recherche simple via ?q=
        $q = trim((string) $request->query->get('q', ''));

        // Pagination via ?page=
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;

        $qb = $userRepository->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC');

        if ($q !== '') {
            $qb->andWhere('u.email LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();

        $users = $qb->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $pages = (int) ceil($total / $limit);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
            'q' => $q,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
        ]);
    }

    #[Route('/{id}/toggle-admin', name: 'admin_users_toggle_admin', methods: ['POST'])]
    public function toggleAdmin(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('toggle_admin_'.$id, (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users_index');
        }

        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('admin_users_index');
        }

        // ✅ Fine-grained : seul SUPER_ADMIN (ou GM via hiérarchie) peut toggler admin
        $this->denyAccessUnlessGranted(UserRoleVoter::TOGGLE_ADMIN, $user);

        // ✅ Rôles stockés uniquement (pas getRoles() !)
        $roles = $user->getStoredRoles();

        if (\in_array('ROLE_ADMIN', $roles, true)) {
            $roles = array_values(array_filter($roles, static fn (string $r) => $r !== 'ROLE_ADMIN'));
            $user->setRoles($roles);
            $this->addFlash('success', 'Rôle admin retiré ✅');
        } else {
            $roles[] = 'ROLE_ADMIN';
            $user->setRoles(array_values(array_unique($roles)));
            $this->addFlash('success', 'Utilisateur promu admin ✅');
        }

        $em->flush();

        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/{id}/toggle-gm', name: 'admin_users_toggle_gm', methods: ['POST'])]
    public function toggleGm(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('toggle_gm_'.$id, (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users_index');
        }

        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('admin_users_index');
        }

        // ✅ Fine-grained + bonus dernier GM géré dans le Voter
        $this->denyAccessUnlessGranted(UserRoleVoter::TOGGLE_GM, $user);

        $roles = $user->getStoredRoles();

        if (\in_array('ROLE_GM', $roles, true)) {
            $roles = array_values(array_filter($roles, static fn (string $r) => $r !== 'ROLE_GM'));
            $user->setRoles($roles);
            $this->addFlash('success', 'Rôle GM retiré ✅');
        } else {
            $roles[] = 'ROLE_GM';
            $user->setRoles(array_values(array_unique($roles)));
            $this->addFlash('success', 'Utilisateur promu GM ✅');
        }

        $em->flush();

        return $this->redirectToRoute('admin_users_index');
    }

    #[Route('/{id}/guild-rank', name: 'admin_users_guild_rank', methods: ['GET', 'POST'])]
    public function editGuildRank(
        User $user,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(UserGuildRankType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Grade mis à jour ✅');

            return $this->redirectToRoute('admin_users_guild_rank', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('admin/users/guild_rank.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
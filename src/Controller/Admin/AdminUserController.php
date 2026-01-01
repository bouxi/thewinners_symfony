<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\UserGuildRankType;
use App\Repository\UserRepository;
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

        // QueryBuilder : on filtre sur email si q est rempli
        $qb = $userRepository->createQueryBuilder('u')
            ->orderBy('u.id', 'DESC');

        if ($q !== '') {
            $qb->andWhere('u.email LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        // Total pour calcul des pages
        $countQb = clone $qb;
        $total = (int) $countQb->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();

        // Résultats paginés
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

        // ✅ Sécurité CSRF : empêche les requêtes forgées
        if (!$this->isCsrfTokenValid('toggle_admin_'.$id, (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('admin_users_index');
        }

        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable.');
            return $this->redirectToRoute('admin_users_index');
        }

        // ✅ On évite de se retirer ses propres droits admin par accident
        $currentUser = $this->getUser();
        if ($currentUser instanceof User && $currentUser->getId() === $user->getId()) {
            $this->addFlash('warning', 'Action refusée : tu ne peux pas modifier ton propre rôle admin ici.');
            return $this->redirectToRoute('admin_users_index');
        }

        /**
         * ⚠️ IMPORTANT :
         * getRoles() ajoute automatiquement ROLE_USER + rôle du guildRank,
         * donc pour stocker en base on doit manipuler uniquement les rôles "bruts".
         *
         * Comme ton User ne propose pas getRawRoles(), on fait une approche safe :
         * - on récupère les rôles stockés via une réflexion : ici on réutilise setRoles()
         * - et on évite d'écrire les rôles injectés.
         *
         * ✅ Solution propre recommandée plus tard : ajouter getStoredRoles(): array dans User.
         */
        $storedRoles = (new \ReflectionClass($user))->getProperty('roles');
        $storedRoles->setAccessible(true);
        $roles = (array) $storedRoles->getValue($user);

        if (in_array('ROLE_ADMIN', $roles, true)) {
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

    /**
     * ✅ Édition du grade de guilde via enum GuildRank.
     */
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

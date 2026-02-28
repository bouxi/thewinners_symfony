<?php

namespace App\Repository;

use App\Entity\User;
use App\Enum\CombatRole;
use App\Enum\GuildRank;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Liste publique des membres de guilde :
     * - Rank != VISITOR
     * - Visible publiquement = true
     * - Filtre optionnel par rôle (Tank/Heal/DPS)
     *
     * @return User[]
     */
    public function findPublicGuildMembers(?CombatRole $role = null): array
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.guildRank <> :visitor')
            ->andWhere('u.isPublicMember = true')
            ->setParameter('visitor', GuildRank::VISITOR);

        if ($role !== null) {
            $qb->andWhere('u.combatRole = :role')
               ->setParameter('role', $role); // enum -> OK
        }

        return $qb
            ->orderBy('u.guildRank', 'ASC')
            ->addOrderBy('u.pseudo', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countUsersWithRole(string $role): int
    {
        // roles est un JSON: on cherche la valeur dedans.
        // Technique simple & portable : LIKE sur la chaîne JSON.
        // Exemple stocké: ["ROLE_USER","ROLE_GM"]
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%"'.$role.'"%')
            ->getQuery()
            ->getSingleScalarResult();
    }

}

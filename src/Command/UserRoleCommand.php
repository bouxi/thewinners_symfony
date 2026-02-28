<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:user:role',
    description: 'Ajoute ou retire un rôle STOCKÉ en base (ROLE_ADMIN, ROLE_GM, ROLE_SUPER_ADMIN, etc.)'
)]
final class UserRoleCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email utilisateur (ex: djbouxi@gmail.com)')
            ->addArgument('action', InputArgument::REQUIRED, 'add | remove')
            ->addArgument('role', InputArgument::REQUIRED, 'Rôle à ajouter/retirer (ex: ROLE_GM)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');
        $action = strtolower((string) $input->getArgument('action'));
        $role = strtoupper((string) $input->getArgument('role'));

        if (!\in_array($action, ['add', 'remove'], true)) {
            $output->writeln('<error>Action invalide. Utilise: add ou remove</error>');
            return Command::INVALID;
        }

        if (!str_starts_with($role, 'ROLE_')) {
            $output->writeln('<error>Le rôle doit commencer par ROLE_ (ex: ROLE_GM)</error>');
            return Command::INVALID;
        }

        $user = $this->users->findOneBy(['email' => $email]);
        if (!$user) {
            $output->writeln(sprintf('<error>Aucun utilisateur trouvé pour %s</error>', $email));
            return Command::FAILURE;
        }

        // ✅ On manipule UNIQUEMENT les rôles stockés en DB
        $storedRoles = $user->getStoredRoles();

        if ($action === 'add') {
            if (!\in_array($role, $storedRoles, true)) {
                $storedRoles[] = $role;
            }
        } else { // remove
            $storedRoles = array_values(array_filter(
                $storedRoles,
                static fn (string $r) => $r !== $role
            ));
        }

        $user->setRoles(array_values(array_unique($storedRoles)));
        $this->em->flush();

        $output->writeln(sprintf(
            "<info>OK</info> %s : rôles stockés => %s | rôles effectifs => %s",
            $email,
            json_encode($user->getStoredRoles(), JSON_UNESCAPED_SLASHES),
            json_encode($user->getRoles(), JSON_UNESCAPED_SLASHES)
        ));

        return Command::SUCCESS;
    }
}

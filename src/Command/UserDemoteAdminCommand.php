<?php

namespace App\Command;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:demote-admin',
    description: 'Retire le rôle ROLE_ADMIN à un utilisateur'
)]
final class UserDemoteAdminCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Email de l’utilisateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = (string) $input->getArgument('email');

        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $io->error("Aucun utilisateur trouvé avec l’email : $email");
            return Command::FAILURE;
        }

        $roles = $user->getRoles();
        if (!in_array('ROLE_ADMIN', $roles, true)) {
            $io->warning('Cet utilisateur n’est pas admin.');
            return Command::SUCCESS;
        }

        $roles = array_values(array_filter($roles, fn($r) => $r !== 'ROLE_ADMIN'));
        $user->setRoles($roles);

        $this->entityManager->flush();

        $io->success("ROLE_ADMIN retiré pour $email ✅");
        return Command::SUCCESS;
    }
}

// php bin/console app:user:demote-admin djbouxi@gmail.com

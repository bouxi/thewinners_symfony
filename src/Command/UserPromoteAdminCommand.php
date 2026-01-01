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
    name: 'app:user:promote-admin',
    description: 'Ajoute le rôle ROLE_ADMIN à un utilisateur'
)]
class UserPromoteAdminCommand extends Command
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // Argument obligatoire : email de l'utilisateur
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l’utilisateur à promouvoir');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        // Recherche de l'utilisateur
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error("Aucun utilisateur trouvé avec l’email : $email");
            return Command::FAILURE;
        }

        // Récupération des rôles existants
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            $io->warning("L’utilisateur est déjà administrateur.");
            return Command::SUCCESS;
        }

        // Ajout du rôle admin
        $roles[] = 'ROLE_ADMIN';
        $user->setRoles(array_unique($roles));

        // Sauvegarde en base
        $this->entityManager->flush();

        $io->success("L’utilisateur $email est maintenant ADMIN ✅");

        return Command::SUCCESS;
    }
}

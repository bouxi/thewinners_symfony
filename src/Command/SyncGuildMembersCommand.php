<?php

declare(strict_types=1);

namespace App\Command;

use App\Enum\ApplicationStatus;
use App\Enum\GuildRank;
use App\Repository\GuildApplicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:sync-guild-members',
    description: 'Synchronise les membres de guilde (isGuildMember + guildRank).',
)]
final class SyncGuildMembersCommand extends Command
{
    public function __construct(
        private readonly GuildApplicationRepository $applicationRepository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $applications = $this->applicationRepository->findAll();

        if (!$applications) {
            $io->warning('Aucune candidature trouvée.');
            return Command::SUCCESS;
        }

        $updatedUsers = 0;

        foreach ($applications as $application) {
            $user = $application->getUser();

            if (!$user) {
                continue;
            }

            $status = $application->getStatus();
            $changed = false;

            // ✅ CAS ACCEPTED
            if ($status === ApplicationStatus::ACCEPTED) {

                if (!$user->isGuildMember()) {
                    $user->setIsGuildMember(true);
                    $changed = true;
                }

                if ($user->getGuildRank() === GuildRank::VISITOR) {
                    $user->setGuildRank(GuildRank::RECRUE);
                    $changed = true;
                }

            } else {
                // ❌ CAS PENDING / REJECTED

                if ($user->isGuildMember()) {
                    $user->setIsGuildMember(false);
                    $changed = true;
                }

                if ($user->getGuildRank() === GuildRank::RECRUE) {
                    $user->setGuildRank(GuildRank::VISITOR);
                    $changed = true;
                }
            }

            if ($changed) {
                $updatedUsers++;
            }
        }

        $this->em->flush();

        $io->success("Synchronisation terminée ✅ ($updatedUsers users mis à jour)");

        return Command::SUCCESS;
    }
}
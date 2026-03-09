<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Security\FileUserStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'users:migrate-db-to-file', description: 'Sync users from database into file storage')]
class UsersMigrateDbToFileCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FileUserStorage $fileUserStorage
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dbUsers = $this->userRepository->findAll();

        $users = [];
        foreach ($dbUsers as $dbUser) {
            $users[$dbUser->getUserIdentifier()] = [
                'password' => $dbUser->getPassword(),
                'roles' => $dbUser->getRoles(),
            ];
        }

        $this->fileUserStorage->replaceUsers($users);

        $output->writeln(sprintf('<info>Migration completed: exported=%d to %s</info>', count($users), $this->fileUserStorage->getFilePath()));

        return Command::SUCCESS;
    }
}

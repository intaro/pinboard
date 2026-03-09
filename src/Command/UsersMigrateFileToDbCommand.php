<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\FileUserStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'users:migrate-file-to-db', description: 'Sync users from file storage into database')]
class UsersMigrateFileToDbCommand extends Command
{
    public function __construct(
        private readonly FileUserStorage $fileUserStorage,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->fileUserStorage->loadUsers();
        if ($users === []) {
            $output->writeln('<comment>No users found in file storage</comment>');

            return Command::SUCCESS;
        }

        $created = 0;
        $updated = 0;

        foreach ($users as $identifier => $row) {
            if (!is_array($row) || !isset($row['password'])) {
                continue;
            }
            $email = isset($row['email']) && is_string($row['email']) && trim($row['email']) !== ''
                ? trim($row['email'])
                : (string)$identifier;

            $roles = $row['roles'] ?? ['ROLE_USER'];
            if (is_string($roles)) {
                $roles = array_map('trim', explode(',', $roles));
            }
            if (!is_array($roles) || $roles === []) {
                $roles = ['ROLE_USER'];
            }

            $user = $this->userRepository->findOneBy(['email' => $email]);
            if (!$user instanceof User) {
                $user = new User();
                $user->setEmail($email);
                ++$created;
            } else {
                ++$updated;
            }

            $user->setRoles($roles);
            $user->setPassword((string)$row['password']);
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $output->writeln(sprintf('<info>Migration completed: created=%d, updated=%d</info>', $created, $updated));

        return Command::SUCCESS;
    }
}

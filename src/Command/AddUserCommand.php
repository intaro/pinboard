<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\FileUserStorage;
use App\Security\LegacyMessageDigestPasswordHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'add-user', description: 'Create or update user in file or database storage')]
class AddUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly FileUserStorage $fileUserStorage,
        private readonly LegacyMessageDigestPasswordHasher $legacyHasher,
        private readonly string $userSource
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'User identifier (email or login)'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'User password in plain text'
            )
            ->addArgument(
                'roles',
                InputArgument::OPTIONAL,
                'Comma-separated roles, e.g. ROLE_USER,ROLE_ADMIN'
            )
            ->addArgument(
                'hosts',
                InputArgument::OPTIONAL,
                'Regexp of server names this user can see, e.g. "site-a\.com|site-b\.com". Default: .* (all)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string)$input->getArgument('username');
        $password = (string)$input->getArgument('password');
        $rolesRaw = (string)$input->getArgument('roles');

        $roles = array_values(array_filter(array_map('trim', explode(',', $rolesRaw))));
        if (count($roles) === 0) {
            $roles = ['ROLE_USER'];
        }

        $hosts = $input->getArgument('hosts');
        $source = strtolower(trim($this->userSource));
        if (!in_array($source, ['file', 'db'], true)) {
            $source = 'file';
        }

        if ($source === 'file') {
            $this->fileUserStorage->upsertUser(
                $email,
                $this->legacyHasher->hash($password),
                $roles,
                is_string($hosts) ? trim($hosts) : null
            );

            $output->writeln(sprintf('<info>User %s created/updated in file %s</info>', $email, $this->fileUserStorage->getFilePath()));

            return Command::SUCCESS;
        }

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $isNew = $user === null;
        if ($user === null) {
            $user = new User();
            $user->setEmail($email);
        }
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setHosts(is_string($hosts) && trim($hosts) !== '' ? trim($hosts) : null);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $action = $isNew ? 'created' : 'updated';
        $output->writeln("<info>User {$email} {$action} successfully in database</info>");

        return Command::SUCCESS;
    }
}

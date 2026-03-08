<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'add-user', description: 'Create or update user in database')]
class AddUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'User email'
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

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);
        $isNew = $user === null;
        if ($user === null) {
            $user = new User();
            $user->setEmail($email);
        }
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $action = $isNew ? 'created' : 'updated';
        $output->writeln("<info>User {$email} {$action} successfully</info>");

        return Command::SUCCESS;
    }
}

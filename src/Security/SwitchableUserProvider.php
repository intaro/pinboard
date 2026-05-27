<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/** @implements UserProviderInterface<FileUser|User> */
class SwitchableUserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FileUserStorage $fileUserStorage,
        private string $source
    ) {
        $this->source = strtolower(trim($this->source));
        if (!\in_array($this->source, ['file', 'db'], true)) {
            $this->source = 'file';
        }
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if ($this->source === 'db') {
            $user = $this->userRepository->findOneBy(['email' => $identifier]);
            if ($user instanceof User) {
                return $user;
            }

            $e = new UserNotFoundException(\sprintf('User "%s" not found in database.', $identifier));
            $e->setUserIdentifier($identifier);
            throw $e;
        }

        if ($identifier === '') {
            $e = new UserNotFoundException('Empty user identifier.');
            $e->setUserIdentifier($identifier);
            throw $e;
        }

        $users = $this->fileUserStorage->loadUsers();
        $storageKey = $identifier;
        $row = $users[$identifier] ?? null;

        if (!\is_array($row)) {
            foreach ($users as $candidateKey => $candidateRow) {
                if (!\is_array($candidateRow) || $candidateKey === '') {
                    continue;
                }

                if (($candidateRow['email'] ?? null) === $identifier) {
                    $storageKey = $candidateKey;
                    $row = $candidateRow;
                    break;
                }
            }
        }

        if (!\is_array($row) || !isset($row['password']) || !\is_string($row['password'])) {
            $e = new UserNotFoundException(\sprintf('User "%s" not found in file storage.', $identifier));
            $e->setUserIdentifier($identifier);
            throw $e;
        }

        $roles = $row['roles'] ?? ['ROLE_USER'];
        if (\is_string($roles)) {
            $roles = array_map('trim', explode(',', $roles));
        }
        if (!\is_array($roles) || $roles === []) {
            $roles = ['ROLE_USER'];
        }

        $hosts = isset($row['hosts']) && \is_string($row['hosts']) ? $row['hosts'] : null;

        return new FileUser(
            $storageKey,
            $row['password'],
            array_values(array_map(static fn (mixed $role): string => is_string($role) ? $role : '', $roles)),
            $hosts
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$this->supportsClass($user::class)) {
            throw new UnsupportedUserException(\sprintf('Unsupported user class "%s".', $user::class));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        if ($this->source === 'db') {
            return \is_a($class, User::class, true);
        }

        return \is_a($class, FileUser::class, true);
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if ($this->source === 'db') {
            if (!$user instanceof User) {
                throw new UnsupportedUserException(\sprintf('Unsupported user class "%s".', $user::class));
            }
            $this->userRepository->upgradePassword($user, $newHashedPassword);

            return;
        }

        if (!$user instanceof FileUser) {
            throw new UnsupportedUserException(\sprintf('Unsupported user class "%s".', $user::class));
        }

        $this->fileUserStorage->upsertUser(
            $user->getUserIdentifier(),
            $newHashedPassword,
            $user->getRoles(),
            $user->getHosts()
        );
    }
}

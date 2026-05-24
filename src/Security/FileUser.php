<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FileUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @param list<string> $roles
     */
    public function __construct(
        private string $identifier,
        private string $password,
        private array $roles = ['ROLE_USER'],
        private ?string $hosts = null
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->identifier;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /** @return list<string> */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_values(array_unique($roles));
    }

    /** @param list<string> $roles */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getHosts(): ?string
    {
        return $this->hosts;
    }

    public function eraseCredentials(): void
    {
    }
}

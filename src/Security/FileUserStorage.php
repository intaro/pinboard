<?php

namespace App\Security;

use Symfony\Component\Yaml\Yaml;

class FileUserStorage
{
    private string $resolvedFilePath;

    public function __construct(string $projectDir, string $filePath)
    {
        $this->resolvedFilePath = $this->resolvePath($projectDir, $filePath);
    }

    public function getFilePath(): string
    {
        return $this->resolvedFilePath;
    }

    public function loadUsers(): array
    {
        $data = $this->loadRawConfig();
        $secure = $data['secure'] ?? null;

        if (!is_array($secure)) {
            return [];
        }

        $users = $secure['users'] ?? [];

        return is_array($users) ? $users : [];
    }

    public function upsertUser(string $identifier, string $hashedPassword, array $roles = ['ROLE_USER'], ?string $hosts = null): void
    {
        $data = $this->loadRawConfig();
        $secure = isset($data['secure']) && is_array($data['secure']) ? $data['secure'] : [];
        $users = isset($secure['users']) && is_array($secure['users']) ? $secure['users'] : [];

        $record = [
            'password' => $hashedPassword,
            'roles' => array_values(array_unique(array_filter(array_map('trim', $roles)))),
        ];

        if ($record['roles'] === []) {
            $record['roles'] = ['ROLE_USER'];
        }

        if ($hosts !== null && $hosts !== '') {
            $record['hosts'] = $hosts;
        }

        $users[$identifier] = $record;
        $secure['enable'] = (bool)($secure['enable'] ?? true);
        $secure['users'] = $users;
        $data['secure'] = $secure;

        $this->saveRawConfig($data);
    }

    public function replaceUsers(array $users): void
    {
        $data = $this->loadRawConfig();
        $secure = isset($data['secure']) && is_array($data['secure']) ? $data['secure'] : [];
        $secure['enable'] = (bool)($secure['enable'] ?? true);
        $secure['users'] = $users;
        $data['secure'] = $secure;

        $this->saveRawConfig($data);
    }

    private function loadRawConfig(): array
    {
        if (!is_file($this->resolvedFilePath)) {
            return [];
        }

        $data = Yaml::parseFile($this->resolvedFilePath);

        return is_array($data) ? $data : [];
    }

    private function saveRawConfig(array $data): void
    {
        $dir = dirname($this->resolvedFilePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $yaml = Yaml::dump($data, 6, 4);
        file_put_contents($this->resolvedFilePath, $yaml);
    }

    private function resolvePath(string $projectDir, string $filePath): string
    {
        if ($filePath === '') {
            return $projectDir . '/config/parameters.yml';
        }

        if (str_starts_with($filePath, '/')) {
            return $filePath;
        }

        return rtrim($projectDir, '/') . '/' . ltrim($filePath, '/');
    }
}

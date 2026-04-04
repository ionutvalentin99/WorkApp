<?php

namespace App\Service;

use App\Repository\AppSettingRepository;

class AppSettingService
{
    public function __construct(
        private readonly AppSettingRepository $repository,
    ) {}

    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->repository->getValue($key, $default ? '1' : '0');
        return $value === '1' || $value === 'true';
    }

    public function get(string $key, string $default = ''): string
    {
        return $this->repository->getValue($key, $default);
    }

    public function setBool(string $key, bool $value): void
    {
        $this->repository->setValue($key, $value ? '1' : '0');
    }

    public function set(string $key, string $value): void
    {
        $this->repository->setValue($key, $value);
    }

    public function isRegistrationEnabled(): bool
    {
        return $this->getBool('registration_enabled', false);
    }
}

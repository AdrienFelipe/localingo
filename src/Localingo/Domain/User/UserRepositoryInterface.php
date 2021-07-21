<?php

declare(strict_types=1);

namespace App\Localingo\Domain\User;

interface UserRepositoryInterface
{
    public function load(string $user_id): ?User;

    public function save(User $user): void;
}

<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Store;

use App\Localingo\Domain\Entity\User;

interface UserStoreInterface
{
    public function load(string $user_id): ?User;

    public function save(User $user): void;
}

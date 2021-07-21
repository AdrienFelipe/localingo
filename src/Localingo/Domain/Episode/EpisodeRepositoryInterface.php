<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Episode;

use App\Localingo\Domain\User\User;

interface EpisodeRepositoryInterface
{
    public function load(User $user, string $episode_id): ?Episode;

    /**
     * @param int|null $expires TTL in seconds
     */
    public function save(Episode $episode, int $expires = null): void;
}

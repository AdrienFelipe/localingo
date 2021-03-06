<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Episode\EpisodeRepositoryInterface;
use App\Localingo\Domain\Episode\Exception\EpisodeVersionException;
use App\Localingo\Domain\User\User;
use ErrorException;
use TypeError;

class EpisodeRedisRepository extends RedisRepository implements EpisodeRepositoryInterface
{
    public function load(User $user, string $episode_id): ?Episode
    {
        $data = (string) $this->redis->get(self::key($user, $episode_id));
        try {
            // TODO: make \__PHP_Incomplete_Class throw an exception from php.ini
            /** @var ?Episode $episode */
            $episode = unserialize($data, ['allow_classes' => true]);
        } catch (ErrorException | TypeError | EpisodeVersionException) {
            return null;
        }

        return is_a($episode, Episode::class) ? $episode : null;
    }

    public function save(Episode $episode, int $expires = null): void
    {
        $key = self::key($episode->getUser(), $episode->getId());
        $this->redis->set($key, serialize($episode));

        // TTL in seconds.
        if ($expires) {
            $this->redis->expire($key, $expires);
        }
    }

    private static function key(User $user, string $episode_id): string
    {
        return "episode:{$user->getId()}:$episode_id";
    }
}

<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Store\Redis;

use App\Localingo\Domain\Episode;
use App\Localingo\Domain\Store\EpisodeStoreInterface;
use App\Localingo\Domain\User;
use Predis\Client;

class EpisodeRedisStore implements EpisodeStoreInterface
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function load(User $user, string $episode_id): ?Episode
    {
        $data = $this->redis->get(self::key($user, $episode_id));
        try {
            $episode = unserialize($data, ['allow_classes' => true]);
        } catch (\Throwable $e) {
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
        return "episode:{$user->getId()}:{$episode_id}";
    }
}
<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Store\Redis;

use App\Localingo\Domain\Entity\Episode;
use App\Localingo\Domain\Entity\User;
use App\Localingo\Domain\Store\EpisodeStoreInterface;
use ErrorException;
use Predis\Client;
use TypeError;

class EpisodeRedisStore implements EpisodeStoreInterface
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function load(User $user, string $episode_id): ?Episode
    {
        $data = (string) $this->redis->get(self::key($user, $episode_id));
        try {
            // TODO: make \__PHP_Incomplete_Class throw an exception from php.ini
            /** @var ?Episode $episode */
            $episode = unserialize($data, ['allow_classes' => true]);
        } catch (ErrorException | TypeError) {
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

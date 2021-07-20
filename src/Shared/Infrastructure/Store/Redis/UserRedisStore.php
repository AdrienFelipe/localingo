<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Store\Redis;

use App\Localingo\Domain\Entity\User;
use App\Localingo\Domain\Store\UserStoreInterface;
use ErrorException;
use Predis\Client;
use TypeError;

class UserRedisStore implements UserStoreInterface
{
    private const KEY_USER_ID = 'user_id';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function load(string $user_id): ?User
    {
        $user_data = $this->redis->get($this->key($user_id));
        try {
            // TODO: make \__PHP_Incomplete_Class throw an exception from php.ini
            $user = unserialize($user_data, ['allowed_classes' => true]);
        } catch (ErrorException | TypeError) {
            return null;
        }

        return is_a($user, User::class) ? $user : null;
    }

    public function save(User $user): void
    {
        $this->redis->set($this->key($user->getId()), serialize($user));
    }

    private function key(string $user_id): string
    {
        return $this::KEY_USER_ID.":{$user_id}";
    }
}

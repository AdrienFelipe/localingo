<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Experience\Exception\ExperienceVersionException;
use App\Localingo\Domain\Experience\Experience;
use App\Localingo\Domain\Experience\ExperienceRepositoryInterface;
use App\Localingo\Domain\User\User;
use ErrorException;
use Predis\Client;
use TypeError;

class ExperienceRedisRepository implements ExperienceRepositoryInterface
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function load(User $user): ?Experience
    {
        $data = (string) $this->redis->get($this->key($user));
        try {
            // TODO: make \__PHP_Incomplete_Class throw an exception from php.ini
            /**
             * @var ?Experience $experience
             */
            $experience = unserialize($data, ['allow_classes' => true]);
        } catch (ErrorException | TypeError | ExperienceVersionException) {
            return null;
        }

        return is_a($experience, Experience::class) ? $experience : null;
    }

    public function save(Experience $experience): void
    {
        $key = $this->key($experience->getUser());
        $this->redis->set($key, serialize($experience));
    }

    private function key(User $user): string
    {
        return "experience:{$user->getId()}";
    }
}

<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use Predis\Client;

class DeclinationRedisRepository implements DeclinationRepositoryInterface
{
    public const DECLINATION_INDEX = 'declination';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function saveAllFromRawData(array $string_declinations): void
    {
        // Remove all previous data first.
        $this->redis->del(self::DECLINATION_INDEX);
        $this->redis->sadd(self::DECLINATION_INDEX, $string_declinations);
    }

    public function getRandom(): string
    {
        return (string) $this->redis->srandmember(self::DECLINATION_INDEX);
    }
}

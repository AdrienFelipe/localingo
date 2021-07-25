<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use Predis\Client;

class DeclinationRedisRepository implements DeclinationRepositoryInterface
{
    private const DECLINATION_INDEX = 'declinations';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function saveFromRawData(array $data): void
    {
        $values = [$data[self::FILE_DECLINATION] => $data[self::FILE_PRIORITY]];
        $this->redis->zadd(self::DECLINATION_INDEX, $values);
    }

    public function getRandom(): string
    {
        $results = (array) $this->redis->zrange(self::DECLINATION_INDEX, 0, 0);

        return (string) reset($results);
    }
}

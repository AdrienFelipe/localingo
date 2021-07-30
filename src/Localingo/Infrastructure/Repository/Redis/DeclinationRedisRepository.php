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

    public function getByPriority(int $limit = 0, array $exclude = []): array
    {
        return RedisTools::sortedScan($this->redis, self::DECLINATION_INDEX, $limit, $exclude);
    }
}

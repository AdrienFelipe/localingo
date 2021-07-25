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

    public function getByPriority(int $limit, array $exclude = []): array
    {
        $cursor = '0';
        $keys = [];
        do {
            $result = (array) $this->redis->zscan(self::DECLINATION_INDEX, $cursor);
            $cursor = (string) ($result[0] ?? '0');
            $values = (array) ($result[1] ?? []);
            $values = array_filter(array_keys($values), static function ($value) use ($exclude) {
                return is_string($value) && !in_array($value, $exclude, true);
            });
            array_push($keys, ...$values);
            if (count($values) >= $limit) {
                break;
            }
        } while ($cursor !== '0');

        return array_splice($values, 0, $limit);
    }
}

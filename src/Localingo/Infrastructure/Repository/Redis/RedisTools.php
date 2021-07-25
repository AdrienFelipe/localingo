<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use Predis\Client;

class RedisTools
{
    private const ITEMS_PER_ITERATION = 10;

    /**
     * While this iterates correctly through all values, it does it in a non-sorted manner.
     * See the 'sortedScan' method for a sorted scan.
     *
     * @return string[]
     */
    public static function findKeys(Client $redis, string $key_pattern, int $limit): array
    {
        $cursor = '0';
        $keys = [];
        do {
            $result = (array) $redis->scan($cursor);
            $cursor = (string) ($result[0] ?? '0');
            $values = (array) ($result[1] ?? []);
            $values = array_filter($values, static function ($value) {
                return is_string($value);
            });
            $values = preg_grep("/$key_pattern/", $values) ?: [];
            array_push($keys, ...$values);
            if (count($keys) >= $limit) {
                break;
            }
        } while ($cursor !== '0');

        return array_splice($keys, 0, $limit);
    }

    /**
     * This requires the objects to be redis sorted sets.
     *
     * @param string[] $exclude
     *
     * @return string[]
     */
    public static function sortedScan(Client $redis, string $key, int $limit, array $exclude = []): array
    {
        $cursor = 0;
        $range = self::ITEMS_PER_ITERATION - 1;
        $values = [];
        while ($results = (array) $redis->zrange($key, $cursor, $range)) {
            $results = array_filter($results, static function ($value) use ($exclude) {
                return is_string($value) && !in_array($value, $exclude, true);
            });
            array_push($values, ...$results);
            if (count($values) >= $limit) {
                break;
            }
            $cursor += self::ITEMS_PER_ITERATION;
            $range += self::ITEMS_PER_ITERATION;
        }

        return array_slice($values, 0, $limit);
    }
}

<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Shared\Domain\Repository\RepositoryInterface;
use Predis\Client;

class RedisRepository implements RepositoryInterface
{
    private const ITEMS_PER_ITERATION = 10;
    protected const SEPARATOR = ':';
    protected const ESCAPER = ';;';

    protected Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function clear(): void
    {
        $this->redis->flushall();
    }

    /**
     * While this iterates correctly through all values, it does it in a non-sorted manner.
     * See the 'sortedScan' method for a sorted scan.
     *
     * @param int      $limit   if 0, returns all
     * @param string[] $exclude
     *
     * @return string[]
     */
    public static function findKeys(Client $redis, string $key_pattern, int $limit, array $exclude = []): array
    {
        $cursor = '0';
        $keys = [];
        do {
            $result = (array) $redis->scan($cursor);
            $cursor = (string) ($result[0] ?? '0');
            $values = (array) ($result[1] ?? []);
            $values = array_filter($values, static function ($value) use ($exclude) {
                return is_string($value) && !in_array($value, $exclude, true);
            });
            $values = preg_grep("/$key_pattern/", $values) ?: [];
            array_push($keys, ...$values);
            if ($limit && count($keys) >= $limit) {
                break;
            }
        } while ($cursor !== '0');

        return $limit ? array_slice($keys, 0, $limit) : $keys;
    }

    /**
     * This requires the objects to be redis sorted sets.
     *
     * @param int      $limit   if 0, returns all
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
            if ($limit && count($values) >= $limit) {
                break;
            }
            $cursor += self::ITEMS_PER_ITERATION;
            $range += self::ITEMS_PER_ITERATION;
        }

        return $limit ? array_slice($values, 0, $limit) : $values;
    }

    /**
     * @param string[]|null[] $data
     *
     * @return string[]
     */
    protected static function unescapeSeparator(array $data): array
    {
        return array_map(static function (?string $value) {
            // Put escaped separator back. Redis empty strings are returned as null values.
            return str_replace(self::ESCAPER, self::SEPARATOR, $value ?? '');
        }, $data);
    }

    /**
     * Generates an escaped key ready to use in redis, or an escaped regex to filter from.
     */
    protected static function keyPatternItem(bool $regex, mixed $item): string
    {
        if (!$item) {
            return $regex ? '[^'.self::SEPARATOR.']*' : '';
        }

        if (is_array($item)) {
            $item = array_map(static function (mixed $value) use ($regex) {
                return self::escapeKeyItem($regex, $value);
            }, $item);

            return '('.implode('|', $item).')';
        }

        return self::escapeKeyItem($regex, $item);
    }

    /**
     * Escape values separator to avoid collisions with internal content.
     * Escape regex special characters for them not to be applied when used in regex mode.
     */
    protected static function escapeKeyItem(bool $regex, mixed $value): string
    {
        $value = str_replace(self::SEPARATOR, self::ESCAPER, (string) $value);

        return $regex ? preg_quote($value, '/') : $value;
    }
}

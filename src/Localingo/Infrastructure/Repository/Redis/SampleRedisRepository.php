<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use Predis\Client;

class SampleRedisRepository implements SampleRepositoryInterface
{
    private const SAMPLE_INDEX = 'sample';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function saveFromRawData(array $data): void
    {
        $key = self::key_pattern(
            $data[self::FILE_WORD],
            $data[self::FILE_DECLINATION],
            $data[self::FILE_NUMBER]
        );
        $this->redis->hmset($key, $data);
    }

    public function load(string $key): Sample
    {
        $fields = [
            self::FILE_DECLINED,
            self::FILE_DECLINATION,
            self::FILE_NUMBER,
            self::FILE_GENDER,
            self::FILE_WORD,
            self::FILE_TRANSLATION,
            self::FILE_STATE,
            self::FILE_CASE,
        ];
        $data = (array) $this->redis->hmget($key, $fields);
        // Redis empty strings are returned as null values.
        $data = array_map(static function (?string $field) {
            return $field ?? '';
        }, $data);
        // Apply fields names.
        $data = array_combine($fields, $data);

        return new Sample(
            $data[self::FILE_DECLINED],
            $data[self::FILE_DECLINATION],
            $data[self::FILE_NUMBER],
            $data[self::FILE_GENDER],
            $data[self::FILE_WORD],
            $data[self::FILE_TRANSLATION],
            $data[self::FILE_STATE],
            $data[self::FILE_CASE],
        );
    }

    public function fromDeclinationAndWords(string $declination, array $words): SampleCollection
    {
        $key_pattern = self::key_pattern(null, $declination);
        $pattern = '/:('.implode('|', $words).'):/';

        $cursor = '0';
        $keys = [];
        do {
            $result = (array) $this->redis->scan($cursor, ['match' => $key_pattern]);
            $cursor = (string) ($result[0] ?? '0');
            $values = (array) ($result[1] ?? []);
            $values = array_filter($values, static function ($value) {
                return is_string($value);
            });
            $values = preg_grep($pattern, $values) ?: [];
            array_push($keys, ...$values);
        } while ($cursor !== '0');

        $samples = [];
        foreach ($keys as $key) {
            $samples[] = $this->load($key);
        }

        return new SampleCollection($samples);
    }

    public static function key_pattern(?string $word, ?string $declination, ?string $number = null): string
    {
        $word !== null or $word = '*';
        $declination !== null or $declination = '*';
        $number !== null or $number = '*';

        return implode(':', [
            self::SAMPLE_INDEX,
            $word,
            $declination,
            $number,
        ]);
    }
}

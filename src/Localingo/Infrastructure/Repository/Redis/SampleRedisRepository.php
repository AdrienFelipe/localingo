<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use Predis\Client;

class SampleRedisRepository implements SampleRepositoryInterface
{
    private const SAMPLE_INDEX = 'declined';
    private const SAMPLE_DECLINED = 'Declined';
    private const SAMPLE_DECLINATION = 'Declination';
    private const SAMPLE_NUMBER = 'Number';
    private const SAMPLE_GENDER = 'Gender';
    private const SAMPLE_WORD = 'Word';
    private const SAMPLE_TRANSLATION = 'Translation';
    private const SAMPLE_STATE = 'State';
    private const SAMPLE_CASE = 'Case';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function saveFromRawData(array $values): void
    {
        $key = self::key_pattern(
            $values[self::SAMPLE_WORD],
            $values[self::SAMPLE_DECLINATION],
            $values[self::SAMPLE_NUMBER]
        );
        $this->redis->hmset($key, $values);
    }

    public function load(string $key): Sample
    {
        $fields = [
            self::SAMPLE_DECLINED,
            self::SAMPLE_DECLINATION,
            self::SAMPLE_NUMBER,
            self::SAMPLE_GENDER,
            self::SAMPLE_WORD,
            self::SAMPLE_TRANSLATION,
            self::SAMPLE_STATE,
            self::SAMPLE_CASE,
        ];
        $data = (array) $this->redis->hmget($key, $fields);
        // Redis empty strings are returned as null values.
        $data = array_map(static function (?string $field) {
            return $field ?? '';
        }, $data);
        // Apply fields names.
        $data = array_combine($fields, $data);

        return new Sample(
            $data[self::SAMPLE_DECLINED],
            $data[self::SAMPLE_DECLINATION],
            $data[self::SAMPLE_NUMBER],
            $data[self::SAMPLE_GENDER],
            $data[self::SAMPLE_WORD],
            $data[self::SAMPLE_TRANSLATION],
            $data[self::SAMPLE_STATE],
            $data[self::SAMPLE_CASE],
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

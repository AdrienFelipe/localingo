<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use Predis\Client;

class SampleRedisRepository implements SampleRepositoryInterface
{
    private const DECLINED_INDEX = 'declined';
    private const DECLINED_DECLINED = 'Declined';
    private const DECLINED_DECLINATION = 'Declination';
    private const DECLINED_NUMBER = 'Number';
    private const DECLINED_GENDER = 'Gender';
    private const DECLINED_WORD = 'Word';
    private const DECLINED_TRANSLATION = 'Translation';
    private const DECLINED_STATE = 'State';
    private const DECLINED_CASE = 'Case';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function load(string $key): Sample
    {
        $fields = [
            self::DECLINED_DECLINED,
            self::DECLINED_DECLINATION,
            self::DECLINED_NUMBER,
            self::DECLINED_GENDER,
            self::DECLINED_WORD,
            self::DECLINED_TRANSLATION,
            self::DECLINED_STATE,
            self::DECLINED_CASE,
        ];
        $data = (array) $this->redis->hmget($key, $fields);
        // Redis empty strings are returned as null values.
        $data = array_map(static function (?string $field) {
            return $field ?? '';
        }, $data);
        // Apply fields names.
        $data = array_combine($fields, $data);

        return new Sample(
            $data[self::DECLINED_DECLINED],
            $data[self::DECLINED_DECLINATION],
            $data[self::DECLINED_NUMBER],
            $data[self::DECLINED_GENDER],
            $data[self::DECLINED_WORD],
            $data[self::DECLINED_TRANSLATION],
            $data[self::DECLINED_STATE],
            $data[self::DECLINED_CASE],
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
        } while ('0' !== $cursor);

        $samples = [];
        foreach ($keys as $key) {
            $samples[] = $this->load($key);
        }

        return new SampleCollection($samples);
    }

    public static function key_pattern(?string $word, ?string $declination, ?string $number = null): string
    {
        null !== $word or $word = '*';
        null !== $declination or $declination = '*';
        null !== $number or $number = '*';

        return implode(':', [
            self::DECLINED_INDEX,
            $word,
            $declination,
            $number,
        ]);
    }
}

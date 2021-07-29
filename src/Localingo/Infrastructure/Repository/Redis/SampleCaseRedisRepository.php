<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\Sample\SampleCaseRepositoryInterface;
use App\Localingo\Domain\Sample\SampleCollection;
use Predis\Client;

class SampleCaseRedisRepository implements SampleCaseRepositoryInterface
{
    private const CASE_INDEX = 'cases';
    private const SEPARATOR = ':';
    private const ESCAPER = ';;';

    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    /**
     * @Warning: affects caseToSample() method.
     */
    public function saveFromRawData(array $data): void
    {
        // TODO: add proper checks.
        $value = $this::valuePattern(
             $data[self::FILE_DECLINATION],
             $data[self::FILE_GENDER],
             $data[self::FILE_NUMBER],
             $data[self::FILE_CASE],
        );
        $this->redis->sadd(self::CASE_INDEX, [$value]);
    }

    /**
     * @Warning: depends on valuePattern() method format.
     * While this might seem duplicated with Experience:caseToSample, it is not.
     * Redis and Yaml are two separate storages.
     */
    public static function caseToSample(string $case): Sample
    {
        $labels = ['declination', 'gender', 'number', 'case'];
        $values = array_combine($labels, explode(self::SEPARATOR, $case));
        // Put escaped separator back.
        array_walk($values, static function (&$value) {
            $value = str_replace(self::ESCAPER, self::SEPARATOR, $value);
        });

        return new Sample(
            '',
            $values['declination'],
            $values['number'],
            $values['gender'],
            '',
            '',
            '',
            $values['case'],
        );
    }

    private static function valuePattern(mixed $declinations, mixed $genders = null, mixed $numbers = null, mixed $cases = null): string
    {
        return implode(self::SEPARATOR, [
            self::valuePatternItem($declinations),
            self::valuePatternItem($genders),
            self::valuePatternItem($numbers),
            self::valuePatternItem($cases),
        ]);
    }

    private static function valuePatternItem(mixed $item): string
    {
        if ($item !== '' && !$item) {
            return '[^'.self::SEPARATOR.']*';
        }

        if (is_array($item)) {
            return '('.implode('|', $item).')';
        }

        // Escape separator.
        return str_replace(self::SEPARATOR, self::ESCAPER, (string) $item);
    }

    public function getCases(array $declinations): SampleCollection
    {
        $samples = new SampleCollection();
        $pattern = self::valuePattern($declinations);
        $results = (array) $this->redis->smembers(self::CASE_INDEX);
        $results = array_filter($results, static function ($result) {return is_string($result); });
        $results = preg_grep("/$pattern/", $results) ?: [];
        foreach ($results as $result) {
            $samples->append(self::caseToSample($result));
        }

        return $samples;
    }
}

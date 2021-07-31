<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Repository\Redis;

use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\Sample\SampleCaseRepositoryInterface;
use App\Localingo\Domain\Sample\SampleCollection;

class SampleCaseRedisRepository extends RedisRepository implements SampleCaseRepositoryInterface
{
    private const CASE_INDEX = 'cases';

    /**
     * @Warning: affects caseToSample() method.
     */
    public function saveFromRawData(array $data): void
    {
        // TODO: add proper checks.
        $value = $this::valuePattern(
            false,
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
    private static function caseToSample(string $case): Sample
    {
        $labels = ['declination', 'gender', 'number', 'case'];
        /** @var string[] $values */
        $values = array_combine($labels, explode(self::SEPARATOR, $case));
        // Put escaped separator back.
        $values = self::unescapeSeparator($values);

        /** @var string[] $values */
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

    private static function valuePattern(bool $regex, mixed $declinations, mixed $genders = null, mixed $numbers = null, mixed $cases = null): string
    {
        return implode(self::SEPARATOR, [
            self::keyPatternItem($regex, $declinations),
            self::keyPatternItem($regex, $genders),
            self::keyPatternItem($regex, $numbers),
            self::keyPatternItem($regex, $cases),
        ]);
    }

    public function getCases(array $declinations): SampleCollection
    {
        $samples = new SampleCollection();
        $pattern = self::valuePattern(true, $declinations);
        $results = (array) $this->redis->smembers(self::CASE_INDEX);
        $results = array_filter($results, static function ($result) {return is_string($result); });
        $results = preg_grep("/$pattern/", $results) ?: [];
        foreach ($results as $result) {
            $samples->append(self::caseToSample($result));
        }

        return $samples;
    }
}

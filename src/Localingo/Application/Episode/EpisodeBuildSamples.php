<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;


use App\Localingo\Application\Word\WordService;
use App\Localingo\Domain\Sample\SampleCollection;

class EpisodeBuildSamples
{
    public function __invoke():SampleCollection{
        // Choose declination.
        $declination = (string) $this->redis->srandmember(WordService::DECLINATION_INDEX);
        // Choose words.
        $words = (array) $this->redis->srandmember(WordService::WORD_INDEX, self::WORDS_BY_EPISODE);
        $key_pattern = WordService::key_pattern(null, $declination);
        $pattern = '/:('.implode('|', $words).'):/';

        $cursor = '0';
        $keys = [];
        do {
            $result = (array) $this->redis->scan($cursor, ['match' => $key_pattern]);
            $cursor = (string) ($result[0] ?? '0');
            $values = (array) ($result[1] ?? []);
            $values = array_filter($values, static function ($value) {return is_string($value); });
            $values = preg_grep($pattern, $values) ?: [];
            array_push($keys, ...$values);
        } while ('0' !== $cursor);

        $samples = [];
        foreach ($keys as $key) {
            $samples[] = $this->wordService->getWord($key);
        }

        return new SampleCollection($samples);

    }
}
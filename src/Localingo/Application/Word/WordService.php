<?php

declare(strict_types=1);

namespace App\Localingo\Application\Word;

use App\Localingo\Domain\Sample\Sample;
use function dump;
use Predis\Client;

class WordService
{
    public const DECLINATION_INDEX = 'declination';
    public const WORD_INDEX = 'word';

    public const DECLINED_INDEX = 'declined';
    private const DECLINED_DECLINED = 'Declined';
    private const DECLINED_DECLINATION = 'Declination';
    private const DECLINED_NUMBER = 'Number';
    private const DECLINED_GENDER = 'Gender';
    private const DECLINED_WORD = 'Word';
    private const DECLINED_TRANSLATION = 'Translation';
    private const DECLINED_STATE = 'State';
    private const DECLINED_CASE = 'Case';

    private Client $store;

    public function __construct(Client $redis)
    {
        $this->store = $redis;
    }

    public function clear(): void
    {
        $this->store->flushall();
    }

    /**
     * Load data to memory.
     */
    public function initialize(): void
    {
        // File hashes to update.
        $update_hashes = [];

        // Check for changes in the files.
        $files = ['declinations.tsv', 'words.tsv'];
        foreach ($files as $filename) {
            $new_hash = hash_file('md5', "/app/files/{$filename}");
            $key = "file:{$filename}:hash";
            $previous_hash = $this->store->get($key);
            if ($new_hash !== $previous_hash) {
                $update_hashes[$key] = $new_hash;
            }
        }

        // Exit as no changes were detected.
        if (empty($update_hashes)) {
            return;
        }

        // Empty to start over.
        $this->clear();

        $handle = fopen('/app/files/declinations.tsv', 'rb');
        if ($handle && $line = fgets($handle)) {
            // Extract headers.
            $header = explode("\t", $line);
            $headerSize = count($header);
            $padArray = array_fill(0, $headerSize, '');
            $declinations = [];
            $words = [];

            $time_start = microtime(true);

            // Extract remaining lines.
            while (($line = fgets($handle)) !== false) {
                $values = explode("\t", $line);
                if (count($values) !== $headerSize) {
                    $values += $padArray;
                }
                $values = array_combine($header, $values);
                $word = $values[self::DECLINED_WORD];
                $declination = $values[self::DECLINED_DECLINATION];
                $key = self::key_pattern($word, $declination, $values[self::DECLINED_NUMBER]);
                $this->store->hmset($key, $values);

                // Build declinations set.
                isset($declinations[$declination]) or $declinations[$declination] = count($declinations);
                // Build words set.
                isset($words[$word]) or $words[$word] = count($words);
            }
            fclose($handle);

            // Save declinations set.
            $this->store->del(self::DECLINATION_INDEX);
            $this->store->sadd(self::DECLINATION_INDEX, array_keys($declinations));

            // Save words set.
            $this->store->del(self::WORD_INDEX);
            $this->store->sadd(self::WORD_INDEX, array_keys($words));

            echo microtime(true) - $time_start;
        } else {
            dump('File error');
        }

        // Save hashes.
        foreach ($update_hashes as $key => $hash) {
            $this->store->set($key, $hash);
        }
    }

    public function getWord(string $key): Sample
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
        $data = $this->store->hmget($key, $fields);
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

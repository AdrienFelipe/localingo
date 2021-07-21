<?php

declare(strict_types=1);

namespace App\Localingo\Application\LocalData;

use App\Localingo\Domain\LocalData\LocalDataRepositoryInterface;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use Predis\Client;

class LocalDataInitialize
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
    private LocalDataRepositoryInterface $dataRepository;
    private SampleRepositoryInterface $sampleRepository;

    public function __construct(Client $redis, LocalDataRepositoryInterface $dataRepository, SampleRepositoryInterface $sampleRepository)
    {
        $this->store = $redis;
        $this->dataRepository = $dataRepository;
        $this->sampleRepository = $sampleRepository;
    }

    /**
     * Load data to memory.
     */
    public function initialize(): void
    {
        // File hashes to update.
        $update_hashes = [];

        // Check for files changes, and if so update hashes.
        $files = ['declinations.tsv', 'words.tsv'];
        foreach ($files as $filename) {
            $new_hash = hash_file('md5', "/app/files/{$filename}");
            $previous_hash = $this->dataRepository->loadFileHash($filename);
            if ($new_hash && $new_hash !== $previous_hash) {
                $update_hashes[$filename] = $new_hash;
            }
        }

        // Exit as no changes were detected.
        if (empty($update_hashes)) {
            return;
        }

        // Empty all to start over.
        $this->dataRepository->clearAllData();

        $handle = fopen('/app/files/declinations.tsv', 'rb');
        if ($handle && $line = fgets($handle)) {
            // Extract headers.
            $header = explode("\t", $line);
            $headerSize = count($header);
            $padArray = array_fill(0, $headerSize, '');
            $declinations = [];
            $words = [];

            // Extract remaining lines.
            while (($line = fgets($handle)) !== false) {
                $values = explode("\t", $line);
                if (count($values) !== $headerSize) {
                    $values += $padArray;
                }
                $values = array_combine($header, $values);
                $word = $values[self::DECLINED_WORD];
                $declination = $values[self::DECLINED_DECLINATION];
                $this->sampleRepository->saveFromRawData($word, $declination, $values[self::DECLINED_NUMBER], $values);

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
        } else {
            // TODO: properly handle error logs.
            echo 'File error';
        }

        // Save hashes.
        foreach ($update_hashes as $filename => $hash) {
            $this->dataRepository->saveFileHash($filename, $hash);
        }
    }
}

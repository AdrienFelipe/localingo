<?php

declare(strict_types=1);

namespace App\Localingo\Application\LocalData;

use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Localingo\Domain\LocalData\LocalDataRepositoryInterface;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Localingo\Domain\Word\WordRepositoryInterface;

class LocalDataInitialize
{
    private const FILES_DIR = '/app/files';
    private const FILES_CHECK = [
        'declinations' => 'declinations.tsv',
        'words' => 'words.tsv',
    ];

    private const COLUMN_DECLINATION = 'Declination';
    private const COLUMN_WORD = 'Word';

    private LocalDataRepositoryInterface $dataRepository;
    private SampleRepositoryInterface $sampleRepository;
    private DeclinationRepositoryInterface $declinationRepository;
    private WordRepositoryInterface $wordRepository;

    public function __construct(LocalDataRepositoryInterface $dataRepository, SampleRepositoryInterface $sampleRepository, DeclinationRepositoryInterface $declinationRepository, WordRepositoryInterface $wordRepository)
    {
        $this->dataRepository = $dataRepository;
        $this->sampleRepository = $sampleRepository;
        $this->declinationRepository = $declinationRepository;
        $this->wordRepository = $wordRepository;
    }

    /**
     * Load data to memory.
     */
    public function __invoke(): void
    {
        // File hashes to update.
        $update_hashes = [];

        // Check for files changes, and if so update hashes.
        foreach (self::FILES_CHECK as $filename) {
            $new_hash = hash_file('md5', self::FILES_DIR."/$filename");
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

        $handle = fopen(self::FILES_DIR.'/'.self::FILES_CHECK['declinations'], 'rb');
        if ($handle && $line = fgets($handle)) {
            // Remove (trailing) line breaks.
            $line = preg_replace('/[\r\n]/', '', $line);
            // Extract headers.
            $header = explode("\t", $line);
            $headerSize = count($header);
            $padArray = array_fill(0, $headerSize, '');
            $declinations = [];
            $words = [];

            // Extract remaining lines.
            while (($line = fgets($handle)) !== false) {
                // Remove (trailing) line breaks.
                $line = preg_replace('/[\r\n]$/', '', $line);

                $values = explode("\t", $line);
                if (count($values) !== $headerSize) {
                    $values += $padArray;
                }
                $values = array_combine($header, $values);
                $this->sampleRepository->saveFromRawData($values);

                // Build declinations set.
                $declination = $values[self::COLUMN_DECLINATION];
                isset($declinations[$declination]) or $declinations[$declination] = count($declinations);

                // Build words set.
                $word = $values[self::COLUMN_WORD];
                isset($words[$word]) or $words[$word] = count($words);
            }
            fclose($handle);

            // Save declinations set.
            $this->declinationRepository->saveAllFromRawData(array_keys($declinations));

            // Save words set.
            $this->wordRepository->saveAllFromRawData(array_keys($words));
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

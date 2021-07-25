<?php

declare(strict_types=1);

namespace App\Localingo\Application\LocalData;

use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Localingo\Domain\LocalData\LocalDataRawInterface;
use App\Localingo\Domain\LocalData\LocalDataRepositoryInterface;
use App\Localingo\Domain\LocalData\ValueObject\LocalDataHeader;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Localingo\Domain\Word\WordRepositoryInterface;

class LocalDataInitialize
{
    private const FILES_DIR = '/app/files';
    private const FILES_CHECK = [
        'declinations' => 'declinations.tsv',
        'words' => 'words.tsv',
        'samples' => 'samples.tsv',
    ];

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

        $files = [
            self::FILES_CHECK['declinations'] => $this->declinationRepository,
            self::FILES_CHECK['words'] => $this->wordRepository,
            self::FILES_CHECK['samples'] => $this->sampleRepository,
        ];
        foreach ($files as $filename => $repository) {
            $this->readFile($filename, $repository);
        }

        // Save hashes.
        foreach ($update_hashes as $filename => $hash) {
            $this->dataRepository->saveFileHash($filename, $hash);
        }
    }

    private function getHeader(string $line): LocalDataHeader
    {
        // Remove (trailing) line breaks.
        $line = preg_replace('/[\r\n]/', '', $line);
        // Extract headers.
        $labels = explode("\t", $line);
        $headerSize = count($labels);
        $padArray = array_fill(0, $headerSize, '');

        return new LocalDataHeader($labels, $headerSize, $padArray);
    }

    /**
     * @return array<string, string>
     */
    private function getValues(string $line, LocalDataHeader $header): array
    {
        // Remove (trailing) line breaks.
        $line = preg_replace('/[\r\n]$/', '', strtolower($line));

        $values = explode("\t", $line);
        // Make sure all rows have the same size.
        if (count($values) !== $header->size) {
            $values += $header->padArray;
        }

        return array_combine($header->labels, $values);
    }

    private function readFile(string $filename, LocalDataRawInterface $repository): void
    {
        $handle = fopen(self::FILES_DIR.'/'.$filename, 'rb');
        if ($handle && $line = fgets($handle)) {
            // Pre-calculate header properties.
            $header = $this->getHeader($line);

            // Extract remaining lines.
            while (($line = fgets($handle)) !== false) {
                $values = $this->getValues($line, $header);
                $repository->saveFromRawData($values);
            }
            fclose($handle);
        } else {
            // TODO: properly handle error logs.
            echo 'File error';
        }
    }
}

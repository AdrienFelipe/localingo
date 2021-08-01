<?php

declare(strict_types=1);

namespace App\Localingo\Application\Dataset;

use App\Localingo\Domain\Dataset\DatasetRawInterface;
use App\Localingo\Domain\Dataset\DatasetRepositoryInterface;
use App\Localingo\Domain\Dataset\DTO\DatasetHeader;
use App\Localingo\Domain\Dataset\Exception\DatasetDirectoryException;
use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Localingo\Domain\Sample\SampleCaseRepositoryInterface;
use App\Localingo\Domain\Sample\SampleCharRepositoryInterface;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Localingo\Domain\Word\WordRepositoryInterface;
use App\Shared\Domain\File\FileInterface;

class DatasetInitialize
{
    private const FILES_LIST = [
        'declinations' => 'declinations.tsv',
        'words' => 'words.tsv',
        'samples' => 'samples.tsv',
    ];
    private const FILES_SEPARATOR = "\t";

    private DatasetRepositoryInterface $datasetRepository;
    private DeclinationRepositoryInterface $declinationRepository;
    private WordRepositoryInterface $wordRepository;
    private SampleRepositoryInterface $sampleRepository;
    private SampleCaseRepositoryInterface $caseRepository;
    private SampleCharRepositoryInterface $charRepository;

    public function __construct(
        DatasetRepositoryInterface $datasetRepository,
        DeclinationRepositoryInterface $declinationRepository,
        WordRepositoryInterface $wordRepository,
        SampleRepositoryInterface $sampleRepository,
        SampleCaseRepositoryInterface $caseRepository,
        SampleCharRepositoryInterface $charRepository,
    ) {
        $this->datasetRepository = $datasetRepository;
        $this->declinationRepository = $declinationRepository;
        $this->wordRepository = $wordRepository;
        $this->sampleRepository = $sampleRepository;
        $this->caseRepository = $caseRepository;
        $this->charRepository = $charRepository;
    }

    /**
     * Load data to memory.
     *
     * @throws DatasetDirectoryException
     */
    public function load(string $filesDirectory = null): void
    {
        // Check files directory. Should not have a trailing slash.
        $filesDirectory !== null or $filesDirectory = (string) ($_ENV[FileInterface::KEY_FILES_DIR] ?? '');
        if (!is_dir($filesDirectory)) {
            throw new DatasetDirectoryException(sprintf('A valid files directory must be set in $_ENV or passed as argument: %s="%s"', FileInterface::KEY_FILES_DIR, $filesDirectory));
        }

        $updated_hashes = [];
        foreach (self::FILES_LIST as $filename) {
            // Hash files to check for changes.
            $new_hash = hash_file('md5', "$filesDirectory/$filename");
            $previous_hash = $this->datasetRepository->loadFileHash($filename);
            // Check for files changes, and if so update hashes.
            if ($new_hash && $new_hash !== $previous_hash) {
                $updated_hashes[$filename] = $new_hash;
            }
        }

        // Exit as no changes were detected.
        if (empty($updated_hashes)) {
            return;
        }

        // Empty all to start over.
        $this->datasetRepository->clearAllData();

        // Multiple repositories can be used on a single file.
        /** @var array<string, DatasetRawInterface[]> $files */
        $files = [
            self::FILES_LIST['declinations'] => [$this->declinationRepository],
            self::FILES_LIST['words'] => [$this->wordRepository],
            self::FILES_LIST['samples'] => [
                $this->sampleRepository,
                $this->caseRepository,
                $this->charRepository,
            ],
        ];
        foreach ($files as $filename => $repositories) {
            $filepath = "$filesDirectory/$filename";
            $this->readFile($filepath, $repositories);
        }

        // Save hashes.
        foreach ($updated_hashes as $filename => $hash) {
            $this->datasetRepository->saveFileHash($filename, $hash);
        }
    }

    private function buildHeader(string $line): DatasetHeader
    {
        // Remove (trailing) line breaks.
        $line = preg_replace('/[\r\n]/', '', $line);
        // Extract headers.
        $labels = explode(self::FILES_SEPARATOR, $line);
        $headerSize = count($labels);
        $padArray = array_fill(0, $headerSize, '');

        return new DatasetHeader($labels, $headerSize, $padArray);
    }

    /**
     * @return array<string, string>
     */
    private function getValues(string $line, DatasetHeader $header): array
    {
        // Remove (trailing) line breaks at the end of the line.
        $line = preg_replace('/[\r\n]$/', '', $line);

        $values = explode(self::FILES_SEPARATOR, $line);
        // Make sure all rows have the same size.
        if (count($values) !== $header->size) {
            $values += $header->padArray;
        }

        // Add label keys to values.
        $values = array_combine($header->labels, $values);
        array_walk($values, static function (string &$value, string $key) {
            // Remove outer spaces. Could be done while cleaning line breaks with / +((?=\t)|$)|(^|(?<=\t)) +/
            // But lets keep it simple with a trim, as this loop is necessary anyway.
            $value = trim($value);
            // Force all values as lowercase except for the 'Case' column.
            $key === DatasetRawInterface::FILE_CASE or $value = strtolower($value);
        });

        /** @var array<string, string> $values */
        return $values;
    }

    /**
     * @param DatasetRawInterface[] $repositories
     */
    private function readFile(string $filepath, array $repositories): void
    {
        $handle = fopen($filepath, 'rb');
        if ($handle && $line = fgets($handle)) {
            // Pre-calculate header properties.
            $header = $this->buildHeader($line);

            // Extract remaining lines.
            while (($line = fgets($handle)) !== false) {
                $values = $this->getValues($line, $header);
                // Reuse same file line on multiple repositories.
                foreach ($repositories as $repository) {
                    $repository->saveFromRawData($values);
                }
            }
            fclose($handle);
        } else {
            // TODO: properly handle error logs.
            echo 'File error';
        }
    }
}

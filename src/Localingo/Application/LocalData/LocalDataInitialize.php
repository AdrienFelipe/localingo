<?php

declare(strict_types=1);

namespace App\Localingo\Application\LocalData;

use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Localingo\Domain\LocalData\Exception\LocalDataDirectoryException;
use App\Localingo\Domain\LocalData\LocalDataRawInterface;
use App\Localingo\Domain\LocalData\LocalDataRepositoryInterface;
use App\Localingo\Domain\LocalData\ValueObject\LocalDataHeader;
use App\Localingo\Domain\Sample\SampleCaseRepositoryInterface;
use App\Localingo\Domain\Sample\SampleCharRepositoryInterface;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Localingo\Domain\Word\WordRepositoryInterface;
use App\Shared\Domain\File\FileInterface;

class LocalDataInitialize
{
    private const FILES_LIST = [
        'declinations' => 'declinations.tsv',
        'words' => 'words.tsv',
        'samples' => 'samples.tsv',
    ];
    private const FILES_SEPARATOR = "\t";

    private LocalDataRepositoryInterface $dataRepository;
    private DeclinationRepositoryInterface $declinationRepository;
    private WordRepositoryInterface $wordRepository;
    private SampleRepositoryInterface $sampleRepository;
    private SampleCaseRepositoryInterface $caseRepository;
    private SampleCharRepositoryInterface $charRepository;

    public function __construct(
        LocalDataRepositoryInterface $dataRepository,
        DeclinationRepositoryInterface $declinationRepository,
        WordRepositoryInterface $wordRepository,
        SampleRepositoryInterface $sampleRepository,
        SampleCaseRepositoryInterface $caseRepository,
        SampleCharRepositoryInterface $charRepository,
    ) {
        $this->dataRepository = $dataRepository;
        $this->declinationRepository = $declinationRepository;
        $this->wordRepository = $wordRepository;
        $this->sampleRepository = $sampleRepository;
        $this->caseRepository = $caseRepository;
        $this->charRepository = $charRepository;
    }

    /**
     * Load data to memory.
     *
     * @throws LocalDataDirectoryException
     */
    public function load(string $filesDirectory = null): void
    {
        // Check files directory. Should not have a trailing slash.
        $filesDirectory !== null or $filesDirectory = (string) ($_ENV[FileInterface::KEY_FILES_DIR] ?? '');
        if (!is_dir($filesDirectory)) {
            throw new LocalDataDirectoryException(sprintf('A valid files directory must be set in $_ENV or passed as argument: %s="%s"', FileInterface::KEY_FILES_DIR, $filesDirectory));
        }

        $updated_hashes = [];
        foreach (self::FILES_LIST as $filename) {
            // Hash files to check for changes.
            $new_hash = hash_file('md5', "$filesDirectory/$filename");
            $previous_hash = $this->dataRepository->loadFileHash($filename);
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
        $this->dataRepository->clearAllData();

        // Multiple repositories can be used on a single file.
        /** @var array<string, LocalDataRawInterface[]> $files */
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
            $this->dataRepository->saveFileHash($filename, $hash);
        }
    }

    private function getHeader(string $line): LocalDataHeader
    {
        // Remove (trailing) line breaks.
        $line = preg_replace('/[\r\n]/', '', $line);
        // Extract headers.
        $labels = explode(self::FILES_SEPARATOR, $line);
        $headerSize = count($labels);
        $padArray = array_fill(0, $headerSize, '');

        return new LocalDataHeader($labels, $headerSize, $padArray);
    }

    /**
     * @return array<string, string>
     */
    private function getValues(string $line, LocalDataHeader $header): array
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
            $key === LocalDataRawInterface::FILE_CASE or $value = strtolower($value);
        });

        /** @var array<string, string> $values */
        return $values;
    }

    /**
     * @param LocalDataRawInterface[] $repositories
     */
    private function readFile(string $filepath, array $repositories): void
    {
        $handle = fopen($filepath, 'rb');
        if ($handle && $line = fgets($handle)) {
            // Pre-calculate header properties.
            $header = $this->getHeader($line);

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

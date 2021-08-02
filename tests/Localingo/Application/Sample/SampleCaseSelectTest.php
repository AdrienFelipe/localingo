<?php

namespace App\Tests\Localingo\Application\Sample;

use App\Localingo\Application\Sample\SampleCaseSelect;
use App\Localingo\Domain\Experience\ValueObject\ExperienceItem;
use App\Localingo\Domain\Sample\Sample;
use App\Localingo\Domain\Sample\SampleCollection;
use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Shared\Application\Test\ApplicationTestCase;
use App\Tests\Localingo\Domain\Experience\ExperienceProvider;

class SampleCaseSelectTest extends ApplicationTestCase
{
    private SampleCaseSelect $sampleCaseSelect;
    private SampleRepositoryInterface $repository;

    public function setUp(): void
    {
        $this->sampleCaseSelect = self::service(SampleCaseSelect::class);
        $this->repository = self::service(SampleRepositoryInterface::class);
    }

    /**
     * @return mixed[][]
     */
    public function sampleExperienceProvider(): array
    {
        return [
            // Check code handles empty data.
            [
                'rawData' => [], 'count' => 5, 'exclude' => [], 'words' => [], '$expected' => [],
            ],
            // All samples with good or non existent experience. Nothing should be returned.
            [
                'rawData' => [
                    ['S1	DeclinationA	NumberA	GenderA	Word1	Translation1	State	End	Case1', 0], // No experience
                    ['S2	DeclinationA	NumberA	GenderA	Word2	Translation2	State	End	Case2', 10], // Good experience
                    ['S3	DeclinationA	NumberA	GenderA	Word3	Translation3	State	End	Case3', 20],
                ],
                'count' => 5, 'exclude' => ['S1'], 'words' => [], '$expected' => [],
            ],
            // Similar samples but different cases. Only samples with bad experience should be returned.
            [
                'rawData' => [
                    ['S1	DeclinationA	NumberA	GenderA	Word1	Translation1	State	End	Case1', 0], // No experience
                    ['S2	DeclinationA	NumberA	GenderA	Word2	Translation2	State	End	Case2', -1], // Bad experience
                    ['S3	DeclinationA	NumberA	GenderA	Word3	Translation3	State	End	Case3', 0],
                ],
                'count' => 2, 'exclude' => ['S1'], 'words' => [], '$expected' => ['S2'],
            ],
            // Multiple cases with bad experience. All should be returned.
            [
                'rawData' => [
                    ['S1	DeclinationA	NumberA	GenderA	Word1	Translation1	State	End	Case1', -5],
                    ['S2	DeclinationA	NumberA	GenderA	Word2	Translation2	State	End	Case2', -1],
                    ['S3	DeclinationA	NumberA	GenderA	Word3	Translation3	State	End	Case3', -10],
                ],
                'count' => 3, 'exclude' => [], 'words' => [], '$expected' => ['S3', 'S1', 'S2'],
            ],
            // Multiple cases with bad experience. Filtering by word.
            [
                'rawData' => [
                    ['S1	DeclinationA	NumberA	GenderA	Word1	Translation1	State	End	Case1', -5],
                    ['S2	DeclinationA	NumberA	GenderA	Word2	Translation2	State	End	Case2', -1],
                    ['S3	DeclinationA	NumberA	GenderA	Word3	Translation3	State	End	Case3', -10],
                ],
                'count' => 3, 'exclude' => [], 'words' => ['Word1'], '$expected' => ['S1'],
            ],
            // Multiple cases with bad experience. Filtering by word and applying exclusion.
            [
                'rawData' => [
                    ['S1	DeclinationA	NumberA	GenderA	Word1	Translation1	State	End	Case1', -5],
                    ['S2	DeclinationA	NumberA	GenderA	Word2	Translation2	State	End	Case2', -1],
                    ['S3	DeclinationA	NumberA	GenderA	Word3	Translation3	State	End	Case3', -10],
                ],
                'count' => 3, 'exclude' => ['S3', 'S1'], 'words' => ['Word1', 'Word2'], '$expected' => ['S2'],
            ],
            // Multiple samples with the same case and a single bad experience. All samples should be selected.
            [
                'rawData' => [
                    ['S1	DeclinationA	NumberA	GenderA	Word1	Translation1	State	End	Case1', 1],
                    ['S2	DeclinationA	NumberA	GenderA	Word2	Translation2	State	End	Case1', 1],
                    ['S3	DeclinationA	NumberA	GenderA	Word3	Translation3	State	End	Case1', -10],
                    ['S4	DeclinationA	NumberA	GenderA	Word4	Translation4	State	End	Case2', ExperienceItem::KNOWN_RATIO],
                ],
                'count' => 10, 'exclude' => [], 'words' => [], '$expected' => ['S1', 'S2', 'S3'],
            ],
            // Multiple samples with the same case and a single bad experience, applying filters.
            [
                'rawData' => [
                    ['S1	DeclinationA	NumberA	GenderA	Word1	Translation1	State	End	Case1', 1],
                    ['S2	DeclinationA	NumberA	GenderA	Word2	Translation2	State	End	Case1', 1],
                    ['S3	DeclinationA	NumberA	GenderA	Word3	Translation3	State	End	Case1', -10],
                    ['S4	DeclinationA	NumberA	GenderA	Word4	Translation4	State	End	Case2', ExperienceItem::KNOWN_RATIO],
                ],
                'count' => 10, 'exclude' => ['S1'], 'words' => ['Word1', 'Word2', 'Word4'], '$expected' => ['S2'],
            ],
            // Multiple samples with the same case and a single bad experience, with limit.
            [
                'rawData' => [
                    ['S1	DeclinationA	NumberA	GenderA	Word1	Translation1	State	End	Case1', 1],
                    ['S2	DeclinationA	NumberA	GenderA	Word2	Translation2	State	End	Case1', 1],
                    ['S3	DeclinationA	NumberA	GenderA	Word3	Translation3	State	End	Case1', -10],
                    ['S4	DeclinationA	NumberA	GenderA	Word4	Translation4	State	End	Case2', ExperienceItem::KNOWN_RATIO],
                ],
                'count' => 2, 'exclude' => [], 'words' => [], '$expected' => ['S3', 'S2'],
            ],
        ];
    }

    /**
     * @dataProvider sampleExperienceProvider
     *
     * @param array[]  $rawData
     * @param string[] $exclude
     * @param string[] $words
     * @param string[] $expected
     */
    public function testSamplesFromExperience(array $rawData, int $count, array $exclude, array $words, array $expected): void
    {
        $experience = ExperienceProvider::empty();
        $excludeCollection = new SampleCollection();
        foreach (array_values($rawData) as [$line, $score]) {
            $data = $this->buildData($line);
            $this->repository->saveFromRawData($data);
            $sample = $this->buildSample($data);
            if (in_array($sample->getDeclined(), $exclude, true)) {
                $excludeCollection->append($sample);
            }
            if ($score < 0) {
                $experience->caseItem($sample)->addBad((int) -$score);
            } elseif ($score > 0) {
                $experience->caseItem($sample)->addGood((int) $score);
            }
        }

        // Tested method.
        $samples = $this->sampleCaseSelect->samplesFromExperience($experience, $count, $excludeCollection, $words);
        $resultDeclined = array_map(static function (Sample $sample) {return $sample->getDeclined(); }, $samples->getArrayCopy());

        $message = "Error on key {$this->dataName()}";
        // Returned order is not forced yet.
        foreach ($expected as $declined) {
            self::assertContains($declined, $resultDeclined, "$message. Sample not returned - Returned: ".implode(',', $resultDeclined));
        }
        self::assertCount(count($expected), $samples, "$message. Too many samples returned - Extra: ".implode(',', array_diff($resultDeclined, $expected)));
    }

    /**
     * @return array<string, string>
     */
    private function buildData(string $line): array
    {
        $values = explode("\t", $line);

        return [
            $this->repository::FILE_DECLINED => $values[0],
            $this->repository::FILE_DECLINATION => $values[1],
            $this->repository::FILE_NUMBER => $values[2],
            $this->repository::FILE_GENDER => $values[3],
            $this->repository::FILE_WORD => $values[4],
            $this->repository::FILE_TRANSLATION => $values[5],
            $this->repository::FILE_STATE => $values[6],
            $this->repository::FILE_CASE => $values[8],
        ];
    }

    /**
     * @param array<string, string> $data
     */
    private function buildSample(array $data): Sample
    {
        return new Sample(
            $data[$this->repository::FILE_DECLINED],
            $data[$this->repository::FILE_DECLINATION],
            $data[$this->repository::FILE_NUMBER],
            $data[$this->repository::FILE_GENDER],
            $data[$this->repository::FILE_WORD],
            $data[$this->repository::FILE_TRANSLATION],
            $data[$this->repository::FILE_STATE],
            $data[$this->repository::FILE_CASE],
        );
    }
}

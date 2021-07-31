<?php

namespace App\Tests\Localingo\Domain\Sample;

use App\Localingo\Domain\Sample\SampleRepositoryInterface;
use App\Shared\Application\Test\ApplicationTestCase;

class SampleRepositoryTest extends ApplicationTestCase
{
    private SampleRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = self::service(SampleRepositoryInterface::class);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rawDataProvider(): array
    {
        return [
            [
                'values' => [
                    'same	same	same	same	same	same	same	same	same',
                    'same	same	same	same	same	same	same	same	same',
                ],
                'expectations' => [
                    // Same values should not be duplicated.
                    0 => ['limit' => 10, 'count' => 1],
                ],
            ],
            [
                'values' => [
                    'declined"A	declination/A	number\A	gender:A	word A	translation\'A	stateA	-a	*',
                    'declined"B	declination/B	number\B	gender:B	word B	translation\'B	stateB	-a	*',
                    'declined"C	declination/C	number\C	gender:C	word C	translation\'C		-a	*',
                    'declined"A	declination/B	number\C	gender:A	word B	translation\'C	stateA	-:a	:*',
                    'declined"C	declination/B	number\A	gender:B	word C	translation\'A		-:a	:*',
                ],
                'expectations' => [
                    // No filter.
                    '1.1' => ['limit' => 10, 'count' => 5],
                    '1.2' => ['limit' => 3, 'count' => 3],
                    // Single-string filters.
                    '2.1' => ['limit' => 10, 'count' => 1, 'words' => 'word A'],
                    '2.2' => ['limit' => 10, 'count' => 3, 'declinations' => 'declination/B'],
                    '2.3' => ['limit' => 10, 'count' => 2, 'numbers' => 'number\C'],
                    '2.4' => ['limit' => 10, 'count' => 1, 'genders' => 'gender:C'],
                    '2.5' => ['limit' => 10, 'count' => 3, 'cases' => '*'],
                    '2.6' => ['limit' => 10, 'count' => 2, 'cases' => ':*'],
                    // Multi-string filters.
                    '3.1' => ['limit' => 10, 'count' => 2, 'words' => 'word B', 'declinations' => 'declination/B', 'numbers' => null, 'genders' => null, 'cases' => null],
                    '3.2' => ['limit' => 10, 'count' => 1, 'words' => null, 'declinations' => 'declination/B', 'numbers' => 'number\C', 'genders' => 'gender:A', 'cases' => null],
                    '3.4' => ['limit' => 10, 'count' => 1, 'words' => 'word A', 'declinations' => null, 'numbers' => null, 'genders' => null, 'cases' => '*'],
                    // Single-array filters.
                    '4.1.0' => ['limit' => 10, 'count' => 5, 'words' => []],
                    '4.1.1' => ['limit' => 10, 'count' => 3, 'words' => ['word A', 'word B', 'word X']],
                    '4.2' => ['limit' => 10, 'count' => 4, 'declinations' => ['declination/A', 'declination/B', 'declination/X']],
                    '4.3' => ['limit' => 10, 'count' => 3, 'numbers' => ['number', 'number\C', 'number\B']],
                    '4.4' => ['limit' => 10, 'count' => 1, 'genders' => ['gender:C', 'gender:C']],
                    '4.5' => ['limit' => 10, 'count' => 5, 'cases' => ['*', ':*']],
                    '4.6' => ['limit' => 2, 'count' => 2, 'cases' => ['*', ':*']],
                    // String and array filters combinations.
                    '5.1' => ['limit' => 10, 'count' => 2, 'words' => [], 'declinations' => 'declination/B', 'numbers' => ['number\A', 'number\B'], 'genders' => null, 'cases' => ['*', ':*']],
                    '5.2' => ['limit' => 1, 'count' => 1, 'words' => [], 'declinations' => 'declination/B', 'numbers' => ['number\A', 'number\B'], 'genders' => null, 'cases' => ['*', ':*']],
                ],
            ],
        ];
    }

    /**
     * @dataProvider rawDataProvider
     *
     * @param array<int, mixed>                $values
     * @param array<int, array<string, mixed>> $expectations
     */
    public function testLoadMultiple(array $values, array $expectations): void
    {
        foreach ($values as $line) {
            $data = $this->buildData($line);
            $this->repository->saveFromRawData($data);
        }

        foreach ($expectations as $key => $expected) {
            $words = $expected['words'] ?? null;
            $declinations = $expected['declinations'] ?? null;
            $numbers = $expected['numbers'] ?? null;
            $genders = $expected['genders'] ?? null;
            $cases = $expected['cases'] ?? null;
            $results = $this->repository->loadMultiple($expected['limit'], $words, $declinations, $genders, $numbers, $cases);

            $message = "Error on key $key";
            self::assertCount($expected['count'], $results, $message);
        }
    }

    public function testValues(): void
    {
        $declined = 'declined';
        $declination = 'declination';
        $number = 'number';
        $gender = 'gender';
        $word = 'word';
        $translation = 'translation';
        $state = 'state';
        $end = 'end';
        $case = 'case';
        $line = "$declined	$declination	$number	$gender	$word	$translation	$state	$end	$case";

        $data = $this->buildData($line);
        $this->repository->saveFromRawData($data);

        $sampleCases = $this->repository->loadMultiple(1, $word, $declination);
        foreach ($sampleCases as $sampleCase) {
            self::assertSame($declined, $sampleCase->getDeclined());
            self::assertSame($declination, $sampleCase->getDeclination());
            self::assertSame($number, $sampleCase->getNumber());
            self::assertSame($gender, $sampleCase->getGender());
            self::assertSame($word, $sampleCase->getWord());
            self::assertSame($translation, $sampleCase->getTranslation());
            self::assertSame($state, $sampleCase->getState());
            // TODO: test "end" value when handled.
            self::assertSame($case, $sampleCase->getCase());
        }
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
}

<?php

declare(strict_types=1);

namespace App\Tests\Localingo\Domain\Sample;

use App\Localingo\Domain\Sample\SampleCaseRepositoryInterface;
use App\Tests\Shared\Infrastructure\Phpunit\ApplicationTestCase;

class SampleCaseRepositoryTest extends ApplicationTestCase
{
    private SampleCaseRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = self::service(SampleCaseRepositoryInterface::class);
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
                    0 => ['declinations' => ['same'], 'count' => 1],
                ],
            ],
            [
                'values' => [
                    'declined"1	declination/A	number\A	gender:A	word 1	translation\'1	state1	X	*:A',   //1 - #A1
                    'declined"2	declination/A	number\A	gender:A	word 2	translation\'2			*:A',       //1 - #A1
                    'declined"3	declination/A	number\A	gender:A	word 3	translation\'3	state3	X	*:B',   //2 - #A2
                    'declined"1	declination/A	number\B	gender:A	word 1	translation\'1	state1		*:A',   //3 - #A3
                    'declined"2	declination/A	number\B	gender:B	word 2	translation\'2	state2	X	*:A',   //4 - #A4
                    'declined"1	declination/B	number\A	gender:A	word 1	translation\'1			*:A',       //5 - #B1
                    'declined"2	declination/B	number\A	gender:A	word 2	translation\'2	state2	X	*:C',   //6 - #B2
                ],
                'expectations' => [
                    1 => ['declinations' => [], 'count' => 6],
                    2 => ['declinations' => ['declination/A'], 'count' => 4],
                    3 => ['declinations' => ['declination/B'], 'count' => 2],
                    4 => ['declinations' => ['declination/A', 'declination/B'], 'count' => 6],
                    5 => ['declinations' => ['declination/C'], 'count' => 0],
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
    public function testGetCases(array $values, array $expectations): void
    {
        foreach ($values as $line) {
            $data = $this->buildData($line);
            $this->repository->saveFromRawData($data);
        }

        foreach ($expectations as $key => $expected) {
            $results = $this->repository->getCases($expected['declinations']);

            $message = "Error on key $key";
            self::assertCount($expected['count'], $results, $message);
        }
    }

    public function testValues(): void
    {
        $declination = 'declination';
        $number = 'number';
        $gender = 'gender';
        $case = 'case';
        $line = "declined	$declination	$number	$gender	word	translation	state	end	$case";

        $data = $this->buildData($line);
        $this->repository->saveFromRawData($data);

        $sampleCases = $this->repository->getCases([]);
        foreach ($sampleCases as $sampleCase) {
            self::assertSame($declination, $sampleCase->getDeclination());
            self::assertSame($number, $sampleCase->getNumber());
            self::assertSame($gender, $sampleCase->getGender());
            self::assertSame($case, $sampleCase->getCase());
            self::assertEmpty($sampleCase->getDeclined());
            self::assertEmpty($sampleCase->getWord());
            self::assertEmpty($sampleCase->getTranslation());
            self::assertEmpty($sampleCase->getState());
            // TODO: test "end" value when handled.
        }
    }

    /**
     * @return array<string, string>
     */
    private function buildData(string $line): array
    {
        $values = explode("\t", $line);

        return [
            $this->repository::FILE_DECLINATION => $values[1],
            $this->repository::FILE_NUMBER => $values[2],
            $this->repository::FILE_GENDER => $values[3],
            $this->repository::FILE_CASE => $values[8],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Localingo\Domain\Sample;

use App\Localingo\Domain\Sample\SampleCharRepositoryInterface;
use App\Tests\Shared\Infrastructure\Phpunit\ApplicationTestCase;

class SampleCharRepositoryTest extends ApplicationTestCase
{
    private SampleCharRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = self::service(SampleCharRepositoryInterface::class);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rawDataProvider(): array
    {
        return [
            [
                'values' => [
                    'stołowi	Dative	Singular	Masculine	stół	table		-C	-C',
                    'pociągowi	Dative	Singular	Masculine	pociąg	train	Impersonal	-C	-C',
                    'rośliną	Instrumental	Singular	Feminine	roślina	plant	Impersonal	-a	*',
                ],
                'expectations' => ['ł', 'ó', 'ą', 'ś'],
            ],
        ];
    }

    /**
     * @dataProvider rawDataProvider
     *
     * @param string[] $values
     * @param string[] $expectations
     */
    public function testLoadList(array $values, array $expectations): void
    {
        foreach ($values as $line) {
            $data = $this->buildData($line);
            $this->repository->saveFromRawData($data);
        }

        $chars = $this->repository->loadList();
        self::assertCount(count($expectations), $chars, 'Incorrect number of extracted special characters');
        foreach ($expectations as $expectation) {
            self::assertContains($expectation, $chars, "Missing character $expectation");
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

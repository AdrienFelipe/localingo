<?php

namespace App\Tests\Localingo\Domain\Word;

use App\Localingo\Domain\Word\WordRepositoryInterface;
use App\Shared\Application\Test\ApplicationTestCase;

class WordRepositoryTest extends ApplicationTestCase
{
    private WordRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = self::service(WordRepositoryInterface::class);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function rawDataProvider(): array
    {
        return [
            [
                'values' => [],
                'expectations' => [
                    0 => ['limit' => 0, 'count' => 0, 'first' => null],
                    1 => ['limit' => 1, 'count' => 0, 'first' => null],
                ],
            ],
            [
                'values' => [['Kobieta', 1], ['Kobieta', 2], ['Biurko', 1], ['Lato', 3]],
                'expectations' => [
                    2 => ['limit' => 0, 'count' => 3, 'first' => 'Biurko'],
                    3 => ['limit' => 1, 'first' => 'Biurko'],
                    4 => ['limit' => 2, 'first' => 'Biurko'],
                    5 => ['limit' => 2, 'first' => 'Kobieta', 'exclude' => ['Biurko']],
                    6 => ['limit' => 0, 'count' => 1, 'first' => 'Biurko', 'exclude' => ['Lato', 'Kobieta']],
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
    public function testRepository(array $values, array $expectations): void
    {
        foreach ($values as $value) {
            $data = $this->buildData($value);
            $this->repository->saveFromRawData($data);
        }

        foreach ($expectations as $key => $expected) {
            $exclude = $expected['exclude'] ?? [];
            $expectedCount = $expected['count'] ?? $expected['limit'];
            $results = $this->repository->getByPriority($expected['limit'], $exclude);

            $message = "Error on key $key";
            self::assertCount($expectedCount, $results, $message);
            self::assertEquals($expected['first'], reset($results), $message);
        }
    }

    /**
     * @param array<int, mixed> $value
     *
     * @return array<string, string>
     */
    public function buildData(array $value): array
    {
        return [
            $this->repository::FILE_WORD => $value[0],
            $this->repository::FILE_PRIORITY => $value[1],
        ];
    }
}

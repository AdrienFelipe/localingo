<?php

namespace App\Tests\Localingo\Domain\Declination;

use App\Localingo\Domain\Declination\DeclinationRepositoryInterface;
use App\Tests\Shared\Infrastructure\Phpunit\ApplicationTestCase;

class DeclinationRepositoryTest extends ApplicationTestCase
{
    private DeclinationRepositoryInterface $repository;

    protected function setUp(): void
    {
        $this->repository = self::service(DeclinationRepositoryInterface::class);
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
                'values' => [['Genitive', 1], ['Genitive', 2], ['Locative', 1], ['Dative', 3]],
                'expectations' => [
                    2 => ['limit' => 0, 'count' => 3, 'first' => 'Locative'],
                    3 => ['limit' => 1, 'first' => 'Locative'],
                    4 => ['limit' => 2, 'first' => 'Locative'],
                    5 => ['limit' => 2, 'first' => 'Genitive', 'exclude' => ['Locative']],
                    6 => ['limit' => 0, 'count' => 1, 'first' => 'Locative', 'exclude' => ['Dative', 'Genitive']],
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
            $this->repository::FILE_DECLINATION => $value[0],
            $this->repository::FILE_PRIORITY => (string) $value[1],
        ];
    }
}

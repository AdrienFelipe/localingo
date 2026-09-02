<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\File\Jsonl;

use App\Localingo\Domain\History\HistoryEntry;
use App\Localingo\Domain\History\HistoryFileInterface;
use App\Localingo\Domain\User\User;
use App\Localingo\Infrastructure\File\LocalFile;

/**
 * One JSON object per line: appending costs nothing and the file stays greppable.
 */
class HistoryJsonlFile extends LocalFile implements HistoryFileInterface
{
    private const string FILENAME = '-history.local.jsonl';

    #[\Override]
    public function append(HistoryEntry $entry): void
    {
        $line = json_encode($entry->serialize(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($line === false) {
            return;
        }

        file_put_contents($this->filepath($entry->user), $line.PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    #[\Override]
    public function readLast(User $user, int $limit): array
    {
        $filepath = $this->filepath($user->getId());
        if (!file_exists($filepath)) {
            return [];
        }

        $lines = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        $entries = [];
        foreach (array_slice(array_reverse($lines), 0, $limit) as $line) {
            $data = json_decode($line, true);
            if (is_array($data)) {
                /** @var array<array-key, mixed> $data */
                $entries[] = self::hydrate($data);
            }
        }

        return array_values(array_filter($entries));
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private static function hydrate(array $data): ?HistoryEntry
    {
        $at = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, self::nullableString($data, 'at') ?? '');
        if ($at === false) {
            return null;
        }

        return new HistoryEntry(
            $at,
            self::nullableString($data, 'user') ?? '',
            self::nullableString($data, 'episode') ?? '',
            self::nullableString($data, 'type') ?? '',
            ($data['correct'] ?? false) === true,
            self::stringMap($data, 'answers'),
            self::stringMap($data, 'expected'),
            self::boolMap($data, 'corrections'),
            self::nullableString($data, 'word'),
            self::nullableString($data, 'declined'),
            self::nullableString($data, 'translation'),
            self::nullableString($data, 'declination'),
            self::nullableString($data, 'number'),
            self::nullableString($data, 'gender'),
            self::nullableString($data, 'case'),
        );
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return array<string, string|null>
     */
    private static function stringMap(array $data, string $key): array
    {
        $values = $data[$key] ?? [];
        if (!is_array($values)) {
            return [];
        }

        $map = [];
        /** @var mixed $value */
        foreach ($values as $name => $value) {
            $map[(string) $name] = is_scalar($value) ? (string) $value : null;
        }

        return $map;
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return array<string, bool>
     */
    private static function boolMap(array $data, string $key): array
    {
        $values = $data[$key] ?? [];
        if (!is_array($values)) {
            return [];
        }

        $map = [];
        /** @var mixed $value */
        foreach ($values as $name => $value) {
            $map[(string) $name] = $value === true;
        }

        return $map;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private static function nullableString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    private function filepath(string $userId): string
    {
        return self::directory().$userId.self::FILENAME;
    }
}

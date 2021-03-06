<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Experience\ValueObject;

use ArrayObject;

/**
 * @extends \ArrayObject<string, ExperienceItem>
 * @psalm-suppress ImplementedReturnTypeMismatch
 *
 * @method ExperienceItem[] getIterator()
 */
class ExperienceItemCollection extends ArrayObject
{
    /**
     * @param array<string, ExperienceItem> $array
     */
    public function __construct($array = [])
    {
        parent::__construct($array);
    }

    public function getOrAdd(string $key): ExperienceItem
    {
        // Get existing item.
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        // Or add a new item as it does not exist.
        $item = new ExperienceItem($key);
        $this->offsetSet($key, $item);

        return $item;
    }

    /**
     * @return array<string, array>
     */
    public function serializeArray(): array
    {
        $data = [];
        foreach ($this->getIterator() as $key => $item) {
            $data[$key] = $item->serialize();
        }

        return $data;
    }

    /**
     * @param array<string, array> $data
     */
    public function unserializeArray(array $data): self
    {
        /** @var array<string, mixed> $values */
        foreach ($data as $key => $values) {
            $item = new ExperienceItem($key);
            $item->unserialize($values);
            $this->offsetSet($key, $item);
        }

        return $this;
    }

    /**
     * Update all items to match current date.
     */
    public function update(): void
    {
        foreach ($this->getIterator() as $item) {
            $item->update();
        }
    }

    /**
     * Get all values which bad/good ratio if greater than zero.
     * Sorted from 'worst' to 'best'.
     *
     * @param ?int $limit 0 or null wil return all elements
     *
     * @psalm-suppress MixedReturnTypeCoercion
     *
     * @return string[]
     */
    public function getRevisionNeeded(int $limit = null): array
    {
        /** @var string[] $support */
        $support = [];
        foreach ($this->getIterator() as $key => $item) {
            $item->isKnow() or $support[$key] = $item->getBadRatio();
        }
        // Sort by 'bad ratio' and keep only the keys.
        arsort($support);
        $support = array_keys($support);

        return $limit ? array_slice($support, 0, $limit) : $support;
    }

    /**
     * @return string[]
     */
    public function getCurrentlyKnown(): array
    {
        /** @var string[] $support */
        $support = [];
        foreach ($this->getIterator() as $key => $item) {
            !$item->isKnow() or $support[] = $key;
        }

        return $support;
    }
}

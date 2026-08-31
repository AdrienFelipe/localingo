<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Experience\ValueObject;

/**
 * @extends \ArrayObject<string, ExperienceItem>
 */
class ExperienceItemCollection extends \ArrayObject
{
    /**
     * @param array<string, ExperienceItem> $array
     */
    public function __construct(array $array = [])
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
     * @return array<string, array<string, mixed>>
     */
    public function serializeArray(): array
    {
        $data = [];
        foreach ($this as $key => $item) {
            $data[$key] = $item->serialize();
        }

        return $data;
    }

    /**
     * @param array<string, array<string, mixed>> $data
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
        foreach ($this as $item) {
            $item->update();
        }
    }

    /**
     * Get all values which bad/good ratio if greater than zero.
     * Sorted from 'worst' to 'best'.
     *
     * @param ?int $limit 0 or null wil return all elements
     *
     * @return string[]
     */
    public function getRevisionNeeded(?int $limit = null): array
    {
        $ratios = [];
        foreach ($this as $key => $item) {
            if (!$item->isKnow()) {
                $ratios[$key] = $item->getBadRatio();
            }
        }
        // Sort by 'bad ratio' and keep only the keys.
        arsort($ratios);
        $support = array_keys($ratios);

        return $limit > 0 ? array_slice($support, 0, $limit) : $support;
    }

    /**
     * @return string[]
     */
    public function getCurrentlyKnown(): array
    {
        $support = [];
        foreach ($this as $key => $item) {
            if ($item->isKnow()) {
                $support[] = $key;
            }
        }

        return $support;
    }
}

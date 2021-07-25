<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Experience\ValueObject;

/**
 * @extends \ArrayObject<string, ExperienceItem>
 * @psalm-suppress ImplementedReturnTypeMismatch
 *
 * @method ExperienceItem[] getIterator()
 */
class ExperienceItemCollection extends \ArrayObject
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
        if ($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }

        // Add a new item as it does not exist.
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
}

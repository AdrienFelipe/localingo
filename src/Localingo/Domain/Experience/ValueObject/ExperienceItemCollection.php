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
}

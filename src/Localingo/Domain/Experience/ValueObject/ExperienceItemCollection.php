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
        if (!$item = $this->offsetGet($key)) {
            $item = new ExperienceItem($key);
            $this->offsetSet($key, $item);
        }

        return $item;
    }
}
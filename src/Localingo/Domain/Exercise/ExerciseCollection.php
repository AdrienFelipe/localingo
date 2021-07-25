<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise;

use ArrayObject;

/**
 * @extends \ArrayObject<string, Exercise>
 *
 * @psalm-suppress ImplementedReturnTypeMismatch
 *
 * @method false|Exercise offsetGet($key)
 * @method Exercise[]     getIterator()
 */
class ExerciseCollection extends ArrayObject
{
    /**
     * @param array<string, Exercise> $exercises
     */
    public function __construct(array $exercises = [])
    {
        parent::__construct($exercises);
    }

    /**
     * Instead of appending to the end of the list, force the use of a non unique id,
     * so that similar items are overridden.
     */
    public function add(Exercise $exercise): void
    {
        $key = $exercise->getKey();
        $this->offsetSet($key, $exercise);
    }

    public function randomKeyFromAvailable(): ?string
    {
        // Keep only 'not done' exercises.
        $exercises = array_filter($this->getArrayCopy(), static function (Exercise $exercise) {
            return !$exercise->getState()->isDone();
        });
        // Exit with null if no exercises were left.
        if (!$keys = array_keys($exercises)) {
            return null;
        }
        $selected = random_int(0, count($keys) - 1);

        return $keys[$selected];
    }
}

<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Exercise;

use ArrayObject;

/**
 * @extends \ArrayObject<int, Exercise>
 *
 * @psalm-suppress ImplementedReturnTypeMismatch
 *
 * @method false|Exercise offsetGet($key)
 */
class ExerciseCollection extends ArrayObject
{
    /**
     * @param array<int, Exercise> $exercises
     */
    public function __construct(array $exercises = [])
    {
        parent::__construct($exercises);
    }

    public function randomKeyFromAvailable(): ?int
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

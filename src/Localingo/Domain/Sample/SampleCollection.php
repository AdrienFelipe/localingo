<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

use ArrayObject;

/**
 * @extends \ArrayObject<int, Sample>
 * @psalm-suppress ImplementedReturnTypeMismatch
 *
 * @method Sample[] getIterator()
 */
class SampleCollection extends ArrayObject
{
    /**
     * @param array<int, Sample> $samples
     */
    public function __construct(array $samples = [])
    {
        parent::__construct($samples);
    }
}

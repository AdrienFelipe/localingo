<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Sample;

/**
 * @method Sample[] getIterator()
 */
class SampleCollection extends \ArrayObject
{
    /**
     * @param Sample[] $samples
     */
    public function __construct(array $samples = [])
    {
        parent::__construct($samples);
    }
}

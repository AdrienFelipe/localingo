<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Dataset\DTO;

class DatasetHeader
{
    /** @var string[] */
    public array $labels;
    public int $size;
    /** @var string[] */
    public array $padArray;

    /**
     * Just a simple DTO for the header values, to avoid recalculating them on each line
     * iteration, and use a type-hinted array.
     *
     * @param string[] $labels
     * @param string[] $padArray
     */
    public function __construct(array $labels, int $size, array $padArray)
    {
        $this->labels = $labels;
        $this->size = $size;
        $this->padArray = $padArray;
    }
}

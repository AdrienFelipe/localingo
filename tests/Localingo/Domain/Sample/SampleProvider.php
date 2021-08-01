<?php

namespace App\Tests\Localingo\Domain\Sample;

use App\Localingo\Domain\Sample\Sample;

class SampleProvider
{
    public static function default(): Sample
    {
        return new Sample(
            'declined',
            'declination',
            'number',
            'gender',
            'word',
            'translation',
            'state',
            'case'
        );
    }
}

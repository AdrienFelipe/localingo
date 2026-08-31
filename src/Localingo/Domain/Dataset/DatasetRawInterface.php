<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Dataset;

interface DatasetRawInterface
{
    public const string FILE_DECLINATION = 'Declination';
    public const string FILE_PRIORITY = 'Priority';
    public const string FILE_DECLINED = 'Declined';
    public const string FILE_NUMBER = 'Number';
    public const string FILE_GENDER = 'Gender';
    public const string FILE_WORD = 'Word';
    public const string FILE_TRANSLATION = 'Translation';
    public const string FILE_STATE = 'State';
    public const string FILE_CASE = 'Case';

    /**
     * @param string[] $data
     */
    public function saveFromRawData(array $data): void;
}

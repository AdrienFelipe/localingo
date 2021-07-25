<?php

declare(strict_types=1);

namespace App\Localingo\Domain\LocalData;

interface LocalDataRawInterface
{
    public const FILE_DECLINATION = 'Declination';
    public const FILE_PRIORITY = 'Priority';
    public const FILE_DECLINED = 'Declined';
    public const FILE_NUMBER = 'Number';
    public const FILE_GENDER = 'Gender';
    public const FILE_WORD = 'Word';
    public const FILE_TRANSLATION = 'Translation';
    public const FILE_STATE = 'State';
    public const FILE_CASE = 'Case';

    /**
     * @param string[] $data
     */
    public function saveFromRawData(array $data): void;
}

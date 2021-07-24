<?php

declare(strict_types=1);

namespace App\Shared\Domain\Templating;

use App\Localingo\Domain\Sample\Sample;

interface TemplatingInterface
{
    public function episodeCard(Sample $sample, mixed $form): Template;

    public function episodeOver(): Template;
}

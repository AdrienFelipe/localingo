<?php

declare(strict_types=1);

namespace App\Shared\Domain\Templating;

use App\Localingo\Domain\Sample\Sample;
use App\Shared\Domain\Templating\ValueObject\Template;

interface TemplatingInterface
{
    public function episodeCard(Sample $sample, mixed $form): Template;

    public function episodeOver(): Template;
}

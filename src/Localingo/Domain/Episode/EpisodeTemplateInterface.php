<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Episode;

use App\Localingo\Domain\Sample\Sample;
use App\Shared\Domain\Templating\Template;

interface EpisodeTemplateInterface
{
    public function episodeCard(Sample $sample, mixed $form): Template;

    public function episodeFinished(): Template;
}

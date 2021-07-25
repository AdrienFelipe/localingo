<?php

declare(strict_types=1);

namespace App\Localingo\Domain\Episode;

use App\Localingo\Domain\Sample\Sample;
use App\Shared\Domain\Templating\Template;

interface EpisodeTemplateInterface
{
    public function declinationCard(Sample $sample, mixed $form): Template;

    public function simpleCard(Sample $sample, mixed $form): Template;

    public function episodeFinished(): Template;
}

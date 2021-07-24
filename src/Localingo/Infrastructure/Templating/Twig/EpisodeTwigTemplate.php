<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Templating\Twig;

use App\Localingo\Domain\Episode\EpisodeTemplateInterface;
use App\Localingo\Domain\Sample\Sample;
use App\Shared\Domain\Templating\Template;

class EpisodeTwigTemplate implements EpisodeTemplateInterface
{
    public function episodeCard(Sample $sample, mixed $form): Template
    {
        return new Template('exercise_card.html.twig', ['sample' => $sample, 'form' => $form]);
    }

    public function episodeOver(): Template
    {
        return new Template('episode_over.html.twig');
    }
}

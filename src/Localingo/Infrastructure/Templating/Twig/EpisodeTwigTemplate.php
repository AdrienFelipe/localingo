<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Templating\Twig;

use App\Localingo\Domain\Episode\EpisodeTemplateInterface;
use App\Localingo\Domain\Sample\Sample;
use App\Shared\Domain\Templating\Template;

class EpisodeTwigTemplate implements EpisodeTemplateInterface
{
    public function declinationCard(Sample $sample, mixed $form): Template
    {
        return new Template('exercise_card_declination.html.twig', ['sample' => $sample, 'form' => $form]);
    }

    public function simpleCard(Sample $sample, mixed $form): Template
    {
        return new Template('exercise_card_simple.html.twig', ['sample' => $sample, 'form' => $form]);
    }

    public function episodeFinished(): Template
    {
        return new Template('episode_over.html.twig');
    }
}

<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Templating\Twig;

use App\Localingo\Domain\Sample\Sample;
use App\Shared\Domain\Templating\TemplatingInterface;
use App\Shared\Domain\Templating\ValueObject\Template;

class TwigTemplates implements TemplatingInterface
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

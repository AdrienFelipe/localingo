<?php

declare(strict_types=1);

namespace App\Localingo\UI\Web;

use App\Localingo\Application\Episode\EpisodeCreate;
use App\Localingo\Application\Episode\EpisodeGetCurrent;
use App\Localingo\Application\LocalData\LocalDataInitialize;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use App\Shared\Domain\Controller\ResponseInterface;

class HomeController
{
    private LocalDataInitialize $dataInitialize;
    private EpisodeGetCurrent $episodeGetCurrent;
    private EpisodeCreate $episodeCreate;
    private ResponseInterface $response;
    private ExerciseFormInterface $homeForm;

    public function __construct(LocalDataInitialize $dataInitialize, EpisodeGetCurrent $episodeGetCurrent, EpisodeCreate $episodeCreate, ResponseInterface $response, ExerciseFormInterface $homeForm)
    {
        $this->dataInitialize = $dataInitialize;
        $this->episodeGetCurrent = $episodeGetCurrent;
        $this->episodeCreate = $episodeCreate;
        $this->response = $response;
        $this->homeForm = $homeForm;
    }

    public function call(): mixed
    {
        // Load data from local files.
        ($this->dataInitialize)();

        // Load current episode or create a new one.
        $episode = ($this->episodeGetCurrent)() ?: ($this->episodeCreate)();

        $sample = $episode->getSamples()->offsetGet(0);

        $template = 'sample_card.html.twig';
        $variables = [
            'sample' => $sample,
            'form' => $this->homeForm->build($sample),
        ];

        return $this->response->build($template, $variables);
    }
}

<?php

declare(strict_types=1);

namespace App\Localingo\UI\Web;

use App\Localingo\Application\Episode\EpisodeCreate;
use App\Localingo\Application\Episode\EpisodeGetCurrent;
use App\Localingo\Application\LocalData\LocalDataInitialize;
use App\Shared\Domain\Controller\ResponseInterface;

class HomeController
{
    private LocalDataInitialize $dataInitialize;
    private EpisodeGetCurrent $episodeGetCurrent;
    private EpisodeCreate $episodeCreate;
    private ResponseInterface $response;

    public function __construct(LocalDataInitialize $dataInitialize, EpisodeGetCurrent $episodeGetCurrent, EpisodeCreate $episodeCreate, ResponseInterface $response)
    {
        $this->dataInitialize = $dataInitialize;
        $this->episodeGetCurrent = $episodeGetCurrent;
        $this->episodeCreate = $episodeCreate;
        $this->response = $response;
    }

    public function call(): mixed
    {
        // Load data to memory.
        $this->dataInitialize->initialize();

        // Load current episode or create a new one.
        $episode = $this->episodeGetCurrent->current() ?: $this->episodeCreate->new();

        $sample = $episode->getSamples()->offsetGet(0);

        $template = 'base.html.twig';
        $variables = [
            'sample' => $sample,
        ];

        return $this->response->build($template, $variables);
    }
}

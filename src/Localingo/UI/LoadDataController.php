<?php

declare(strict_types=1);

namespace App\Localingo\UI;

use App\Localingo\Application\Episode\EpisodeCreate;
use App\Localingo\Application\Episode\EpisodeGetCurrent;
use App\Localingo\Application\Word\WordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LoadDataController extends AbstractController
{
    private WordService $wordService;
    private EpisodeGetCurrent $episodeGetCurrent;
    private EpisodeCreate $episodeCreate;

    public function __construct(WordService $wordService, EpisodeGetCurrent $episodeGetCurrent, EpisodeCreate $episodeCreate)
    {
        $this->wordService = $wordService;
        $this->episodeGetCurrent = $episodeGetCurrent;
        $this->episodeCreate = $episodeCreate;
    }

    public function call(): Response
    {
        // Load data to memory.
        $this->wordService->initialize();

        // Load current episode or create a new one.
        $episode = $this->episodeGetCurrent->current() ?: $this->episodeCreate->new();

        $sample = $episode->getSamples()->offsetGet(0);

        return $this->render('base.html.twig', [
            'sample' => $sample,
        ]);
    }
}

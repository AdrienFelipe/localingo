<?php

declare(strict_types=1);

namespace App\Localingo\UI;

use App\Localingo\Application\Episode\EpisodeService;
use App\Localingo\Application\Word\WordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LoadDataController extends AbstractController
{
    private WordService $wordService;
    private EpisodeService $episodeInitialize;

    public function __construct(WordService $wordService, EpisodeService $courseService)
    {
        $this->wordService = $wordService;
        $this->episodeInitialize = $courseService;
    }

    public function call(): Response
    {
        // Load data to memory.
        $this->wordService->initialize();

        // Load current episode or create a new one.
        $episode = $this->episodeInitialize->current() ?: $this->episodeInitialize->new();
        $this->episodeInitialize->save($episode);

        $sample = $episode->getSamples()->offsetGet(0);

        return $this->render('base.html.twig', [
            'sample' => $sample,
        ]);
    }
}

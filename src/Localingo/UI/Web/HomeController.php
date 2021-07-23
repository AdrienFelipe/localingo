<?php

declare(strict_types=1);

namespace App\Localingo\UI\Web;

use App\Localingo\Application\Episode\EpisodeCreate;
use App\Localingo\Application\Episode\EpisodeGetCurrent;
use App\Localingo\Application\Exercise\ExerciseBuildForm;
use App\Localingo\Application\Exercise\ExerciseGetCorrections;
use App\Localingo\Application\LocalData\LocalDataInitialize;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use App\Localingo\Domain\Exercise\ValueObject\ExerciseType;
use App\Shared\Domain\Controller\ResponseInterface;

class HomeController
{
    private LocalDataInitialize $dataInitialize;
    private EpisodeGetCurrent $episodeGetCurrent;
    private EpisodeCreate $episodeCreate;
    private ResponseInterface $response;
    private ExerciseFormInterface $exerciseForm;
    private ExerciseGetCorrections $exerciseValidation;
    private ExerciseBuildForm $exerciseBuildForm;

    public function __construct(LocalDataInitialize $dataInitialize, EpisodeGetCurrent $episodeGetCurrent, EpisodeCreate $episodeCreate, ResponseInterface $response, ExerciseFormInterface $exerciseForm, ExerciseGetCorrections $exerciseValidation, ExerciseBuildForm $exerciseBuildForm)
    {
        $this->dataInitialize = $dataInitialize;
        $this->episodeGetCurrent = $episodeGetCurrent;
        $this->episodeCreate = $episodeCreate;
        $this->response = $response;
        $this->exerciseForm = $exerciseForm;
        $this->exerciseValidation = $exerciseValidation;
        $this->exerciseBuildForm = $exerciseBuildForm;
    }

    public function call(): mixed
    {
        // Load data from local files.
        ($this->dataInitialize)();

        // Load current episode or create a new one.
        $episode = ($this->episodeGetCurrent)() ?: ($this->episodeCreate)();
        $sample = $episode->getSamples()->offsetGet(0);

        $exercise = new Exercise(ExerciseType::declined(), $sample);

        $template = 'exercise_card.html.twig';
        $variables = [
            'sample' => $sample,
            'form' => ($this->exerciseBuildForm)($exercise),
        ];

        return $this->response->build($template, $variables);
    }

    public function getExerciseForm(): ExerciseFormInterface
    {
        return $this->exerciseForm;
    }

    public function getExerciseValidation(): ExerciseGetCorrections
    {
        return $this->exerciseValidation;
    }
}

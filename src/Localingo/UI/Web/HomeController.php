<?php

declare(strict_types=1);

namespace App\Localingo\UI\Web;

use App\Localingo\Application\Episode\EpisodeCreate;
use App\Localingo\Application\Episode\EpisodeGetCurrent;
use App\Localingo\Application\Exercise\ExerciseValidation;
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
    private ExerciseValidation $exerciseValidation;

    public function __construct(LocalDataInitialize $dataInitialize, EpisodeGetCurrent $episodeGetCurrent, EpisodeCreate $episodeCreate, ResponseInterface $response, ExerciseFormInterface $exerciseForm, ExerciseValidation $exerciseValidation)
    {
        $this->dataInitialize = $dataInitialize;
        $this->episodeGetCurrent = $episodeGetCurrent;
        $this->episodeCreate = $episodeCreate;
        $this->response = $response;
        $this->exerciseForm = $exerciseForm;
        $this->exerciseValidation = $exerciseValidation;
    }

    public function call(): mixed
    {
        // Load data from local files.
        ($this->dataInitialize)();

        // Load current episode or create a new one.
        $episode = ($this->episodeGetCurrent)() ?: ($this->episodeCreate)();
        $sample = $episode->getSamples()->offsetGet(0);

        $exercise = new Exercise(ExerciseType::declined(), $sample);

        $this->exerciseForm->initialize($exercise);
        if (!$this->exerciseForm->isSubmitted()) {
            // Form was not yet submitted: display exercise questions.
            /** @psalm-suppress MixedAssignment */
            $form = $this->exerciseForm->buildExerciseForm($exercise);
        } else {
            // Form was submitted: display corrections.
            $submittedDTO = $this->exerciseForm->getSubmitted();
            $corrections = $this->exerciseValidation->getCorrections($exercise, $submittedDTO);
            /** @psalm-suppress MixedAssignment */
            $form = $this->exerciseForm->buildAnswersForm($exercise, $corrections);
        }

        $template = 'exercise_card.html.twig';
        $variables = [
            'sample' => $sample,
            'form' => $form,
        ];

        return $this->response->build($template, $variables);
    }
}

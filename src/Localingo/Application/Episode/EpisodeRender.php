<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\LocalData\LocalDataInitialize;
use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Episode\EpisodeTemplateInterface;
use App\Localingo\Domain\Episode\ValueObject\EpisodeState;
use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use App\Shared\Domain\Templating\Template;

class EpisodeRender
{
    private LocalDataInitialize $dataInitialize;
    private EpisodeGet $episodeGet;
    private EpisodeCreate $episodeCreate;
    private ExerciseFormInterface $exerciseForm;
    private EpisodeTemplateInterface $episodeTemplate;
    private EpisodeExecute $episodeExecute;

    public function __construct(LocalDataInitialize $dataInitialize, EpisodeGet $episodeGet, EpisodeCreate $episodeCreate, EpisodeExecute $episodeExecute, ExerciseFormInterface $exerciseForm, EpisodeTemplateInterface $episodeTemplate)
    {
        $this->dataInitialize = $dataInitialize;
        $this->episodeGet = $episodeGet;
        $this->episodeCreate = $episodeCreate;
        $this->exerciseForm = $exerciseForm;
        $this->episodeTemplate = $episodeTemplate;
        $this->episodeExecute = $episodeExecute;
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    public function getTemplate(): Template
    {
        // Load data from local files.
        ($this->dataInitialize)();

        // Load current episode or create a new one.
        $episode = $this->getEpisode();
        $exercise = $this->getExercise($episode);

        // No exercises are left, go to finish page.
        if (!$exercise) {
            return $this->applyFinished($episode);
        }

        // Simplify conditional reading.
        $exerciseIsNotNew = !$exercise->getState()->isNew();
        $formWasNotSubmitted = !$this->exerciseForm->isSubmitted($exercise);
        $episodeIsQuestion = $episode->getState()->isQuestion();

        // Render and display the 'question' template.
        if ($exerciseIsNotNew && ($formWasNotSubmitted || $episodeIsQuestion)) {
            return $this->applyQuestion($exercise);
        }

        // Render and display the 'answer' template.
        return $this->applyAnswer($exercise);
    }

    private function getEpisode(): Episode
    {
        $episode = $this->episodeGet->current();
        if (!$episode || $episode->getState()->isFinished()) {
            $episode = $this->episodeCreate->new();
        }

        return $episode;
    }

    private function getExercise(Episode $episode): ?Exercise
    {
        $exercise = $episode->getCurrentExercise();

        if (!$exercise || $episode->getState()->isNext()) {
            $exercise = $episode->nextExercise();
            $episode->setState(EpisodeState::question());
        }

        return $exercise;
    }

    private function applyQuestion(Exercise $exercise): Template
    {
        /** @psalm-suppress MixedAssignment */
        $form = $this->exerciseForm->buildExerciseForm($exercise);
        $this->episodeExecute->applyQuestion($exercise->getEpisode());

        return $this->selectCard($exercise, $form);
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    private function applyAnswer(Exercise $exercise): Template
    {
        // Form was submitted: display corrections.
        $answers = $this->exerciseForm->getSubmitted($exercise);
        $corrections = $this->episodeExecute->applyAnswer($exercise, $answers);
        /** @psalm-suppress MixedAssignment */
        $form = $this->exerciseForm->buildAnswersForm($exercise, $corrections);

        return $this->selectCard($exercise, $form);
    }

    private function applyFinished(Episode $episode): Template
    {
        $this->episodeExecute->applyFinished($episode);

        return $this->episodeTemplate->episodeFinished();
    }

    private function selectCard(Exercise $exercise, mixed $form): Template
    {
        if ($exercise->getType()->isDeclined()) {
            return $this->episodeTemplate->declinationCard($exercise->getSample(), $form);
        }

        return $this->episodeTemplate->simpleCard($exercise->getSample(), $form);
    }
}

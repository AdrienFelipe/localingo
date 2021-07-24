<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\Exercise\ExerciseGetCorrections;
use App\Localingo\Application\LocalData\LocalDataInitialize;
use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Episode\ValueObject\EpisodeState;
use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use App\Shared\Domain\Templating\Template;
use App\Shared\Domain\Templating\TemplatingInterface;

class EpisodeExecute
{
    private LocalDataInitialize $dataInitialize;
    private EpisodeGet $episodeGet;
    private EpisodeCreate $episodeCreate;
    private EpisodeSave $episodeSave;
    private ExerciseFormInterface $exerciseForm;
    private ExerciseGetCorrections $exerciseValidation;
    private TemplatingInterface $templating;

    public function __construct(LocalDataInitialize $dataInitialize, EpisodeGet $episodeGet, EpisodeCreate $episodeCreate, EpisodeSave $episodeSave, ExerciseFormInterface $exerciseForm, ExerciseGetCorrections $exerciseValidation, TemplatingInterface $templating)
    {
        $this->dataInitialize = $dataInitialize;
        $this->episodeGet = $episodeGet;
        $this->episodeCreate = $episodeCreate;
        $this->episodeSave = $episodeSave;
        $this->exerciseForm = $exerciseForm;
        $this->exerciseValidation = $exerciseValidation;
        $this->templating = $templating;
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
            return $this->getFinished($episode);
        }

        // Simplify conditional reading.
        $exerciseIsNotNew = !$exercise->getState()->isNew();
        $formWasNotSubmitted = !$this->exerciseForm->isSubmitted($exercise);
        $episodeIsQuestion = $episode->getState()->isQuestion();

        // Render and display the 'question' template.
        if ($exerciseIsNotNew && ($formWasNotSubmitted || $episodeIsQuestion)) {
            return $this->getQuestion($episode, $exercise);
        }

        // Render and display the 'answer' template.
        return $this->getAnswer($episode, $exercise);
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

    private function getQuestion(Episode $episode, Exercise $exercise): Template
    {
        /** @psalm-suppress MixedAssignment */
        $form = $this->exerciseForm->buildExerciseForm($exercise);
        // Update episode state.
        $episode->setState(EpisodeState::answer());
        $this->episodeSave->apply($episode);

        return $this->templating->episodeCard($exercise->getSample(), $form);
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    private function getAnswer(Episode $episode, Exercise $exercise): Template
    {
        // Form was submitted: display corrections.
        $submittedDTO = $this->exerciseForm->getSubmitted($exercise);
        $corrections = ($this->exerciseValidation)($exercise, $submittedDTO);
        /** @psalm-suppress MixedAssignment */
        $form = $this->exerciseForm->buildAnswersForm($exercise, $corrections);
        // Update episode state.
        $episode->setState(EpisodeState::next());
        // Update exercise state.
        $isCorrect = !in_array(false, $corrections, true);
        $isCorrect ? $exercise->nextState() : $exercise->previousState();
        $this->episodeSave->apply($episode);

        return $this->templating->episodeCard($exercise->getSample(), $form);
    }

    private function getFinished(Episode $episode): Template
    {
        $episode->setState(EpisodeState::finished());
        $this->episodeSave->apply($episode);

        return $this->templating->episodeOver();
    }
}

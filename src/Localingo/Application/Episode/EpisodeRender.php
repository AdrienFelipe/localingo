<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\Dataset\DatasetInitialize;
use App\Localingo\Domain\Dataset\Exception\DatasetDirectoryException;
use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Episode\EpisodeTemplateInterface;
use App\Localingo\Domain\Episode\ValueObject\EpisodeState;
use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use App\Localingo\Domain\Sample\SampleCharRepositoryInterface;
use App\Shared\Domain\Templating\Template;

class EpisodeRender
{
    private DatasetInitialize $dataInitialize;
    private EpisodeGet $episodeGet;
    private EpisodeCreate $episodeCreate;
    private ExerciseFormInterface $exerciseForm;
    private EpisodeTemplateInterface $episodeTemplate;
    private EpisodeExecute $episodeExecute;
    private SampleCharRepositoryInterface $charRepository;

    public function __construct(
        DatasetInitialize $dataInitialize,
        EpisodeGet $episodeGet,
        EpisodeCreate $episodeCreate,
        EpisodeExecute $episodeExecute,
        ExerciseFormInterface $exerciseForm,
        EpisodeTemplateInterface $episodeTemplate,
        SampleCharRepositoryInterface $charRepository,
    ) {
        $this->dataInitialize = $dataInitialize;
        $this->episodeGet = $episodeGet;
        $this->episodeCreate = $episodeCreate;
        $this->exerciseForm = $exerciseForm;
        $this->episodeTemplate = $episodeTemplate;
        $this->episodeExecute = $episodeExecute;
        $this->charRepository = $charRepository;
    }

    /**
     * @throws ExerciseMissingStateOrder
     * @throws DatasetDirectoryException
     */
    public function getTemplate(): Template
    {
        // Load data from local files.
        $this->dataInitialize->load();

        // Load current episode and exercise, or create new ones.
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
        $chars = $this->charRepository->loadList();

        return $this->selectCard($exercise, $form, $chars);
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

    /**
     * @param string[] $chars
     */
    private function selectCard(Exercise $exercise, mixed $form, array $chars = []): Template
    {
        if ($exercise->getType()->isDeclined()) {
            return $this->episodeTemplate->declinationCard($exercise->getSample(), $form, $chars);
        }

        return $this->episodeTemplate->simpleCard($exercise->getSample(), $form, $chars);
    }
}

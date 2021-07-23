<?php

declare(strict_types=1);

namespace App\Localingo\UI\Web;

use App\Localingo\Application\Episode\EpisodeCreate;
use App\Localingo\Application\Episode\EpisodeGet;
use App\Localingo\Application\Episode\EpisodeSave;
use App\Localingo\Application\Exercise\ExerciseBuildForm;
use App\Localingo\Application\Exercise\ExerciseGetCorrections;
use App\Localingo\Application\LocalData\LocalDataInitialize;
use App\Localingo\Domain\Episode\ValueObject\EpisodeState;
use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use App\Shared\Domain\Controller\ResponseInterface;

class HomeController
{
    private LocalDataInitialize $dataInitialize;
    private EpisodeGet $episodeGet;
    private EpisodeCreate $episodeCreate;
    private EpisodeSave $episodeSave;
    private ResponseInterface $response;
    private ExerciseFormInterface $exerciseForm;
    private ExerciseGetCorrections $exerciseValidation;
    private ExerciseBuildForm $exerciseBuildForm;

    public function __construct(LocalDataInitialize $dataInitialize, EpisodeGet $episodeGet, EpisodeCreate $episodeCreate, EpisodeSave $episodeSave, ResponseInterface $response, ExerciseFormInterface $exerciseForm, ExerciseGetCorrections $exerciseValidation, ExerciseBuildForm $exerciseBuildForm)
    {
        $this->dataInitialize = $dataInitialize;
        $this->episodeGet = $episodeGet;
        $this->episodeCreate = $episodeCreate;
        $this->episodeSave = $episodeSave;
        $this->response = $response;
        $this->exerciseForm = $exerciseForm;
        $this->exerciseValidation = $exerciseValidation;
        $this->exerciseBuildForm = $exerciseBuildForm;
    }

    /**
     * @throws ExerciseMissingStateOrder
     */
    public function call(): mixed
    {
        // Load data from local files.
        ($this->dataInitialize)();

        // Load current episode or create a new one.
        $episode = $this->episodeGet->current();
        if (!$episode || $episode->getState()->is_finished()) {
            $episode = $this->episodeCreate->new();
        }
        $exercise = $episode->getCurrentExercise();

        // Only display exercises that are not done yet.
        if (!$exercise || $episode->getState()->is_next()) {
            $exercise = $episode->nextExercise();
            $episode->setState(EpisodeState::question());
        }

        // No exercises are left, go to finish page.
        if (!$exercise || true) {
            $episode->setState(EpisodeState::finished());

            return $this->response->build('episode_over.html.twig');
        }

        // Form MUST first be initialized.
        $this->exerciseForm->initialize($exercise);
        // Form was not yet submitted: display exercise questions.
        if (!$this->exerciseForm->isSubmitted() || $episode->getState()->is_question()) {
            /** @psalm-suppress MixedAssignment */
            $form = $this->exerciseForm->buildExerciseForm($exercise);
            // Update episode state.
            $episode->setState(EpisodeState::answer());
        } else {
            // Form was submitted: display corrections.
            $submittedDTO = $this->exerciseForm->getSubmitted();
            $corrections = ($this->exerciseValidation)($exercise, $submittedDTO);
            /** @psalm-suppress MixedAssignment */
            $form = $this->exerciseForm->buildAnswersForm($exercise, $corrections);
            // Update episode state.
            $episode->setState(EpisodeState::next());
            // Update exercise state.
            $isCorrect = !in_array(false, $corrections, true);
            $isCorrect ? $exercise->nextState() : $exercise->previousState();
        }

        $this->episodeSave->apply($episode);

        $template = 'exercise_card.html.twig';
        $variables = [
            'sample' => $exercise->getSample(),
            'form' => $form,
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

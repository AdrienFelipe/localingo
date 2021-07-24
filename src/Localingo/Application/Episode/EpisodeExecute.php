<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\Exercise\ExerciseGetCorrections;
use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Episode\ValueObject\EpisodeState;
use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseDTO;

class EpisodeExecute
{
    private EpisodeSave $episodeSave;
    private ExerciseGetCorrections $exerciseValidation;

    public function __construct(EpisodeSave $episodeSave, ExerciseGetCorrections $exerciseValidation)
    {
        $this->episodeSave = $episodeSave;
        $this->exerciseValidation = $exerciseValidation;
    }

    public function applyQuestion(Episode $episode): void
    {
        $episode->setState(EpisodeState::answer());
        $this->episodeSave->apply($episode);
    }

    /**
     * @return array<string, bool>
     *
     * @throws ExerciseMissingStateOrder
     */
    public function applyAnswer(Exercise $exercise, ExerciseDTO $answers): array
    {
        $corrections = ($this->exerciseValidation)($exercise, $answers);
        // Update episode state.
        $exercise->getEpisode()->setState(EpisodeState::next());
        // Update exercise state.
        $isCorrect = !in_array(false, $corrections, true);
        $isCorrect ? $exercise->nextState() : $exercise->previousState();
        $this->episodeSave->apply($exercise->getEpisode());

        return $corrections;
    }

    public function applyFinished(Episode $episode): void
    {
        $episode->setState(EpisodeState::finished());
        $this->episodeSave->apply($episode);
    }
}

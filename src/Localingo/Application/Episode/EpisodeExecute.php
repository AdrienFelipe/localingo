<?php

declare(strict_types=1);

namespace App\Localingo\Application\Episode;

use App\Localingo\Application\Exercise\ExerciseExecute;
use App\Localingo\Application\Exercise\ExerciseValidate;
use App\Localingo\Application\Experience\ExperienceExecute;
use App\Localingo\Application\History\HistoryRecord;
use App\Localingo\Domain\Episode\Episode;
use App\Localingo\Domain\Episode\ValueObject\EpisodeState;
use App\Localingo\Domain\Exercise\Exception\ExerciseMissingStateOrder;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseDTO;

class EpisodeExecute
{
    private EpisodeSave $episodeSave;
    private ExerciseValidate $exerciseValidate;
    private ExerciseExecute $exerciseExecute;
    private ExperienceExecute $experienceExecute;
    private HistoryRecord $historyRecord;

    public function __construct(EpisodeSave $episodeSave, ExerciseValidate $exerciseValidate, ExerciseExecute $exerciseExecute, ExperienceExecute $experienceExecute, HistoryRecord $historyRecord)
    {
        $this->episodeSave = $episodeSave;
        $this->exerciseValidate = $exerciseValidate;
        $this->exerciseExecute = $exerciseExecute;
        $this->experienceExecute = $experienceExecute;
        $this->historyRecord = $historyRecord;
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
        // Update exercise state.
        $corrections = $this->exerciseValidate->getCorrections($exercise, $answers);
        $isCorrect = $this->exerciseValidate->isCorrect($corrections);
        $this->historyRecord->answered($exercise, $answers, $corrections, $isCorrect);
        $this->exerciseExecute->applyAnswer($exercise, $isCorrect);

        // Update episode state.
        $episode = $exercise->getEpisode();
        $episode->setState(EpisodeState::next());
        $this->episodeSave->apply($episode);

        return $corrections;
    }

    public function applyFinished(Episode $episode): void
    {
        $episode->setState(EpisodeState::finished());
        $this->episodeSave->apply($episode);
        $this->experienceExecute->applyFinished($episode);
    }
}

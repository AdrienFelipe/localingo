<?php

declare(strict_types=1);

namespace App\Localingo\Application\Exercise;

use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;

class ExerciseBuildForm
{
    private ExerciseFormInterface $exerciseForm;
    private ExerciseGetCorrections $exerciseValidation;

    public function __construct(ExerciseFormInterface $exerciseForm, ExerciseGetCorrections $exerciseValidation)
    {
        $this->exerciseForm = $exerciseForm;
        $this->exerciseValidation = $exerciseValidation;
    }

    public function __invoke(Exercise $exercise): mixed
    {
        $this->exerciseForm->initialize($exercise);
        if (!$this->exerciseForm->isSubmitted()) {
            // Form was not yet submitted: display exercise questions.
            /** @psalm-suppress MixedAssignment */
            $form = $this->exerciseForm->buildExerciseForm($exercise);
        } else {
            // Form was submitted: display corrections.
            $submittedDTO = $this->exerciseForm->getSubmitted();
            $corrections = ($this->exerciseValidation)($exercise, $submittedDTO);
            /** @psalm-suppress MixedAssignment */
            $form = $this->exerciseForm->buildAnswersForm($exercise, $corrections);
        }

        return $form;
    }
}

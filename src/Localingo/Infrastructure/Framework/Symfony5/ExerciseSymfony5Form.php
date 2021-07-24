<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Framework\Symfony5;

use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseDTO;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class ExerciseSymfony5Form extends AbstractController implements ExerciseFormInterface
{
    private const STYLE_ROW_BIG = 'big';
    private const STYLE_ROW_NORMAL = 'normal';

    /**
     * @psalm-suppress TooManyTemplateParams
     *
     * @var ?FormInterface <string, FormInterface>
     */
    private ?FormInterface $form = null;

    public function initialize(Exercise $exercise): void
    {
        $request = Request::createFromGlobals();
        $this->form = $this->formBuilder($exercise)->getForm();
        $this->form->handleRequest($request);
    }

    public function isSubmitted(Exercise $exercise): bool
    {
        // Initialize form if not done yet.
        if ($this->form === null) {
            $this->initialize($exercise);
        }

        /** @psalm-suppress PossiblyNullReference */
        return $this->form->isSubmitted() && $this->form->isValid();
    }

    public function getSubmitted(Exercise $exercise): ExerciseDTO
    {
        // Initialize form if not done yet.
        if ($this->form === null) {
            $this->initialize($exercise);
        }

        /**
         * @psalm-suppress PossiblyNullReference
         *
         * @var ExerciseDTO
         */
        return $this->form->getData();
    }

    /**
     * @psalm-suppress TooManyTemplateParams
     *
     * @return FormView<mixed>
     */
    public function buildExerciseForm(Exercise $exercise): FormView
    {
        return $this->formBuilder($exercise, true)->getForm()->createView();
    }

    public function buildAnswersForm(Exercise $exercise, array $corrections): FormView
    {
        return $this->formBuilder($exercise, false, $corrections)->getForm()->createView();
    }

    /**
     * @param array<string, bool> $corrections
     *
     * @psalm-suppress TooManyTemplateParams
     *
     * @return FormBuilderInterface<mixed>
     */
    private function formBuilder(Exercise $exercise, bool $isExercise = true, array $corrections = []): FormBuilderInterface
    {
        $questions = $exercise->getQuestions();
        $exerciseDTO = $exercise->getDTO($isExercise);
        $builder = $this->createFormBuilder($exerciseDTO);

        // Exercise values.
        $properties = $exercise->getDTO()->asPropertyNames();
        $this->addTextRow($builder, (string) $properties->translation, self::STYLE_ROW_BIG, $isExercise, $questions, $corrections);
        $this->addTextRow($builder, (string) $properties->declined, self::STYLE_ROW_BIG, $isExercise, $questions, $corrections);
        $this->addTextRow($builder, (string) $properties->word, self::STYLE_ROW_NORMAL, $isExercise, $questions, $corrections);
        $this->addTextRow($builder, (string) $properties->gender, self::STYLE_ROW_NORMAL, $isExercise, $questions, $corrections);
        $this->addTextRow($builder, (string) $properties->state, self::STYLE_ROW_NORMAL, $isExercise, $questions, $corrections);
        $this->addTextRow($builder, (string) $properties->case, self::STYLE_ROW_NORMAL, $isExercise, $questions, $corrections);

        // Submit button.
        $this->addSubmitRow($builder, $isExercise);

        return $builder;
    }

    /**
     * @psalm-suppress TooManyTemplateParams
     *
     * @param FormBuilderInterface<mixed> $builder
     * @param string[]                    $questions
     * @param array<string, bool>         $corrections
     */
    private function addTextRow(FormBuilderInterface $builder, string $name, string $style, bool $isExercise, array $questions, array $corrections): void
    {
        $style_classes = $style === self::STYLE_ROW_BIG ? 'form-control-lg text-success' : 'form-control-sm bg-white';
        $isQuestion = in_array($name, $questions, true);

        if ($isExercise || !$isQuestion) {
            $correction_classes = '';
        } else {
            $correction_classes = $corrections[$name] ?? true ? 'is-valid' : 'is-invalid';
        }
        $floatingConf = [
            'required' => false,
            'attr' => [
                'class' => "border-0 rounded-0 bg-light $style_classes $correction_classes",
                'disabled' => !($isExercise && $isQuestion),
                'readonly' => !($isExercise && $isQuestion),
                'autocomplete' => 'off',
                'autofocus ' => true,
            ],
            'row_attr' => [
                'class' => 'form-floating mb-1 border-top',
            ],
        ];
        $builder->add($name, TextType::class, $floatingConf);
    }

    /**
     * @psalm-suppress TooManyTemplateParams
     *
     * @param FormBuilderInterface<mixed> $builder
     */
    private function addSubmitRow(FormBuilderInterface $builder, bool $isExercise): void
    {
        $label = $isExercise ? 'Check' : 'Next';
        $class = $isExercise ? 'btn-primary' : 'btn-success';
        $builder->add('next', SubmitType::class, [
            'label' => $label,
            'attr' => ['class' => "$class btn"],
            'row_attr' => ['class' => 'p-2 d-grid border-top'],
        ]);
    }
}

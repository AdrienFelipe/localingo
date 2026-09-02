<?php

declare(strict_types=1);

namespace App\Localingo\Framework\Symfony;

use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseDTO;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class ExerciseSymfonyForm extends AbstractController implements ExerciseFormInterface
{
    private const string STYLE_ROW_BIG = 'big';
    private const string STYLE_ROW_NORMAL = 'normal';
    private const string STYLE_ROW_HIDDEN = 'hidden';

    // Content language of each field. Browsers use it for dictation and spellcheck,
    // but no web API can switch the mobile keyboard layout.
    private const string LANG_TRANSLATION = 'en';
    private const string LANG_LEARNED = 'pl';

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

    #[\Override]
    public function isSubmitted(Exercise $exercise): bool
    {
        // Initialize form if not done yet.
        if ($this->form === null) {
            $this->initialize($exercise);
        }

        /** @psalm-suppress PossiblyNullReference */
        return $this->form->isSubmitted() && $this->form->isValid();
    }

    #[\Override]
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
    #[\Override]
    public function buildExerciseForm(Exercise $exercise): FormView
    {
        $questions = $exercise->getQuestions();
        $questions = array_combine($questions, array_fill(0, count($questions), true));

        return $this->formBuilder($exercise, true, $questions)->getForm()->createView();
    }

    #[\Override]
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
        $questions = array_keys($corrections);
        $exerciseDTO = $exercise->getDTO($isExercise);
        $builder = $this->createFormBuilder($exerciseDTO);

        $type = $exercise->getType();
        // Differentiate between declination or translation exercises (simple or declination card).
        if ($type->isWord() || $type->isTranslation()) {
            $declinedStyle = self::STYLE_ROW_HIDDEN;
            $wordStyle = self::STYLE_ROW_BIG;
            $caseStyle = self::STYLE_ROW_HIDDEN;
        } else {
            $declinedStyle = self::STYLE_ROW_BIG;
            $wordStyle = self::STYLE_ROW_NORMAL;
            $caseStyle = self::STYLE_ROW_NORMAL;
        }

        // Exercise values.
        $properties = $exercise->getDTO()->asPropertyNames();
        $this->addTextRow($builder, (string) $properties->translation, self::STYLE_ROW_BIG, $isExercise, $questions, $corrections, self::LANG_TRANSLATION);
        $this->addTextRow($builder, (string) $properties->declined, $declinedStyle, $isExercise, $questions, $corrections, self::LANG_LEARNED);
        $this->addTextRow($builder, (string) $properties->word, $wordStyle, $isExercise, $questions, $corrections, self::LANG_LEARNED);
        $this->addTextRow($builder, (string) $properties->gender, self::STYLE_ROW_NORMAL, $isExercise, $questions, $corrections, self::LANG_TRANSLATION);
        $this->addTextRow($builder, (string) $properties->state, self::STYLE_ROW_NORMAL, $isExercise, $questions, $corrections, self::LANG_TRANSLATION);
        $this->addTextRow($builder, (string) $properties->case, $caseStyle, $isExercise, $questions, $corrections, self::LANG_TRANSLATION);

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
     * @param string                      $lang        content language, used by dictation and spellcheck (it cannot switch the mobile keyboard)
     */
    private function addTextRow(FormBuilderInterface $builder, string $name, string $style, bool $isExercise, array $questions, array $corrections, string $lang): void
    {
        $style_classes = $style === self::STYLE_ROW_BIG ? 'form-control-lg text-success' : 'form-control-sm bg-white';
        $isQuestion = in_array($name, $questions, true);
        $isEditable = $isExercise && $isQuestion;

        if ($isExercise || !$isQuestion) {
            $correction_classes = '';
        } else {
            $correction_classes = $corrections[$name] ?? true ? 'is-valid' : 'is-invalid';
        }
        $floatingConf = [
            'required' => false,
            'attr' => [
                'class' => "border-0 rounded-0 bg-light $style_classes $correction_classes",
                'disabled' => !$isEditable,
                'readonly' => !$isEditable,
                'autocomplete' => 'off',
                'autofocus' => $isEditable,
                'lang' => $lang,
                // Labels the mobile return key as "go", so it submits the card.
                'enterkeyhint' => 'go',
                // Mobile keyboards would otherwise capitalize and "correct" the answer.
                'autocapitalize' => 'off',
                'autocorrect' => 'off',
                'spellcheck' => 'false',
            ],
            'row_attr' => [
                'class' => 'form-floating mb-1 border-top',
            ],
        ];
        $type = $style === self::STYLE_ROW_HIDDEN ? HiddenType::class : TextType::class;
        $builder->add($name, $type, $floatingConf);
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

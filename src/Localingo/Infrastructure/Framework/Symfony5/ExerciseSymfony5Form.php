<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Framework\Symfony5;

use App\Localingo\Application\Exercise\ExerciseValidation;
use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseDTO;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use App\Localingo\Domain\Exercise\ValueObject\ExerciseType;
use App\Localingo\Domain\Sample\Sample;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class ExerciseSymfony5Form extends AbstractController implements ExerciseFormInterface
{
    private ExerciseValidation $exerciseValidation;

    public function __construct(ExerciseValidation $exerciseValidation)
    {
        $this->exerciseValidation = $exerciseValidation;
    }

    public function build(Sample $sample): FormView
    {
        $request = Request::createFromGlobals();
        $exercise = new Exercise(ExerciseType::declined(), $sample);

        $builder = $this->formBuilder($exercise);
        $form = $builder->getForm();

        $form->handleRequest($request);
        // Form was submitted.
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ExerciseDTO $submittedDTO */
            $submittedDTO = $form->getData();
            $corrections = $this->exerciseValidation->getCorrections($exercise, $submittedDTO);
            // Build a brand new form with all the values.
            $builder = $this->formBuilder($exercise, $corrections);
            foreach ($corrections as $property => $isCorrect) {
                $item = $builder->get($property);
                $options = $builder->get($property)->getOptions();
                $class = $isCorrect ? 'is-valid' : 'is-invalid';
                /** @psalm-suppress MixedArrayAccess **/
                if (is_string($options['attr']['class'] ?? null)) {
                    /** @psalm-suppress MixedOperand, MixedArrayAssignment **/
                    $options['attr']['class'] .= " $class";
                } else {
                    /** @psalm-suppress MixedArrayAssignment **/
                    $options['attr']['class'] = $class;
                }
                $builder->remove($property);
                $type = $item->getType()->getInnerType()::class;
                $builder->add($property, $type, $options);
            }
            $form = $builder->getForm();
        }

        return $form->createView();
    }

    /**
     * @param array<string, bool>|null $corrections
     *
     * @psalm-suppress TooManyTemplateParams
     *
     * @return FormBuilderInterface<mixed,mixed>
     */
    private function formBuilder(Exercise $exercise, array $corrections = null): FormBuilderInterface
    {
        $isExercise = !$corrections;
        $exerciseDTO = $exercise->getDTO($isExercise);

        $floatingConf = [
            'required' => false,
            'attr' => [
                'class' => 'border-0 rounded-0 bg-light',
                'disabled' => true,
                'readonly' => true,
            ],
            'row_attr' => [
                'class' => 'form-floating mb-1 border-top',
            ],
        ];
        $whiteConf = $bigConf = $floatingConf;
        $bigConf['attr']['class'] .= ' form-control-lg text-success';
        $whiteConf['attr']['class'] .= ' form-control-sm bg-white';

        // Empty fields will be editable.
        $translationConf = $declinedConf = $bigConf;
        $wordConf = $floatingConf;

        // Translation input setup.
        $isTranslation = $exercise->getType()->isTranslation() && $isExercise;
        $translationConf['attr'] = array_merge($translationConf['attr'], ['disabled' => !$isTranslation, 'readonly' => !$isTranslation]);

        // Declined input setup.
        $isDeclined = $exercise->getType()->isDeclined() && $isExercise;
        $declinedConf['attr'] = array_merge($declinedConf['attr'], ['disabled' => !$isDeclined, 'readonly' => !$isDeclined]);

        return $this->createFormBuilder($exerciseDTO)
            ->add('translation', TextType::class, $translationConf)
            ->add('declined', TextType::class, $declinedConf)
            ->add('word', TextType::class, $wordConf)
            ->add('gender', TextType::class, $whiteConf)
            ->add('state', TextType::class, $whiteConf)
            ->add('case', TextType::class, $whiteConf)
            ->add('next', SubmitType::class, [
                'label' => 'Check',
                'row_attr' => ['class' => 'p-2 d-grid border-top'],
            ]);
    }
}

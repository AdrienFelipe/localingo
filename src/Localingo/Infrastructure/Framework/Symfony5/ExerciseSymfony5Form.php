<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Framework\Symfony5;

use App\Localingo\Domain\Exercise\Exercise;
use App\Localingo\Domain\Exercise\ExerciseDTO;
use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use App\Localingo\Domain\Exercise\ValueObject\ExerciseType;
use App\Localingo\Domain\Sample\Sample;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class ExerciseSymfony5Form extends AbstractController implements ExerciseFormInterface
{
    public function build(Sample $sample): FormView
    {
        $request = Request::createFromGlobals();
        $exercise = new Exercise(ExerciseType::translation(), $sample);

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
        $isTranslation = $exercise->getType()->isTranslation();
        $translationConf['attr'] = array_merge($translationConf['attr'], ['disabled' => !$isTranslation, 'readonly' => !$isTranslation]);

        // Declined input setup.
        $isDeclined = $exercise->getType()->isDeclined();
        $declinedConf['attr'] = array_merge($declinedConf['attr'], ['disabled' => !$isDeclined, 'readonly' => !$isDeclined]);
        $wordConf['attr'] = array_merge($wordConf['attr'], ['disabled' => !$isDeclined, 'readonly' => !$isDeclined]);

        $builder = $this->createFormBuilder($exercise->getDTO())
            ->add('translation', TextType::class, $translationConf)
            ->add('declined', TextType::class, $declinedConf)
            ->add('word', TextType::class, $wordConf)
            ->add('gender', TextType::class, $whiteConf)
            ->add('state', TextType::class, $whiteConf)
            ->add('case', TextType::class, $whiteConf)
            ->add('next', SubmitType::class, [
                'label' => 'Check',
                'row_attr' => ['class' => 'p-2 d-grid'],
            ]);
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ExerciseDTO $submittedDTO */
            $submittedDTO = $form->getData();
            /** @var ?mixed $value */
            foreach ((array) $submittedDTO as $property => $value) {
                if (!$value) {
                    $submittedDTO->$property = $exercise->getDTO()->$property;
                }
            }

            $form = $builder->getForm();
        }

        return $form->createView();
    }
}

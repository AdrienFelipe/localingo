<?php

declare(strict_types=1);

namespace App\Localingo\Infrastructure\Framework\Symfony5;

use App\Localingo\Domain\Exercise\ExerciseFormInterface;
use App\Localingo\Domain\Sample\Sample;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use function dump;

class ExerciseSymfony5Form extends AbstractController implements ExerciseFormInterface
{
    public function build(Sample $sample): FormView
    {
        $request = Request::createFromGlobals();

        $floatingConf = [
            'attr' => [
                'class' => 'border-0 rounded-0 bg-light',
                //'disabled' => true,
               // 'readonly' => true,
            ],
            'row_attr' => [
                'class' => 'form-floating mb-1 border-top',
            ],
        ];
        $whiteConf = $bigConf = $floatingConf;
        $bigConf['attr']['class'] .= ' form-control-lg text-success';
        $whiteConf['attr']['class'] .= ' form-control-sm bg-white';

        $builder = $this->createFormBuilder($sample)
            ->add('translation', TextType::class, $bigConf)
            ->add('declined', TextType::class, $bigConf)
            ->add('word', TextType::class, $floatingConf)
            ->add('gender', TextType::class, $whiteConf)
            ->add('state', TextType::class, $whiteConf)
            ->add('case', TextType::class, $whiteConf)
            ->add('save', SubmitType::class, ['label' => 'Create Task']);
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Sample $sample */
            $sample = $form->getData();
            dump($sample);

            $sample->setCase('GGGGG');
            $form = $builder->getForm();
        }

        return $form->createView();
    }
}

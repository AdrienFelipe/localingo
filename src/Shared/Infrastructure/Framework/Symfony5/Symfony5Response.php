<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Framework\Symfony5;

use App\Localingo\Domain\Sample\Sample;
use App\Shared\Domain\Controller\ResponseInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;

class Symfony5Response extends AbstractController implements ResponseInterface
{
    public function build(string $template, array $variables, Sample $sample): Response
    {
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

        $form = $this->createFormBuilder($sample)
            ->add('translation', TextType::class, $bigConf)
            ->add('declined', TextType::class, $bigConf)
            ->add('word', TextType::class, $floatingConf)
            ->add('gender', TextType::class, $whiteConf)
            ->add('state', TextType::class, $whiteConf)
            ->add('case', TextType::class, $whiteConf)

            ->add('save', SubmitType::class, ['label' => 'Create Task'])
            ->getForm();

        $variables['form'] = $form->createView();

        return $this->render($template, $variables);
    }
}

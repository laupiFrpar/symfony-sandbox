<?php

namespace Lopi\Form;

use Lopi\Entity\Category2;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Category2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'data' => $options['data']->translate('en_US')->getName(),
                'mapped' => false,
            ])
            ->add('description', TextType::class, [
                'data' => $options['data']->translate('en_US')->getDescription(),
                'mapped' => false,
            ])
            ->add('someFieldYouDoNotNeedToTranslate')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category2::class,
        ]);
    }
}

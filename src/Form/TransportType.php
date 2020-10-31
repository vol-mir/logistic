<?php

namespace App\Form;

use App\Entity\Transport;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('number', TextType::class, [
                'label' => 'label.number',
                'attr' => [
                    'placeholder' => 'label.number',
                    'title' => 'label.number',
                    'class' => 'form-control',
                    'name' => 'transport_number'
                ]
            ])
            ->add('marka', TextType::class, [
                'label' => 'label.marka',
                'attr' => [
                    'placeholder' => 'label.marka',
                    'title' => 'label.marka',
                    'class' => 'form-control',
                    'name' => 'transport_marka'
                ]
            ])
            ->add('model', TextType::class, [
                'label' => 'label.model',
                'attr' => [
                    'placeholder' => 'label.model',
                    'title' => 'label.model',
                    'class' => 'form-control',
                    'name' => 'transport_model'
                ]
            ])
            ->add('kind', ChoiceType::class, [
                'choices' => array_flip(Transport::KINDS),
                'label' => 'label.kind',
                'attr' => [
                    'placeholder' => 'label.kind',
                    'title' => 'label.kind',
                    'class' => 'form-control',
                    'name' => 'transport_kind'
                ]
            ])
            ->add('carrying', NumberType::class, [
                'label' => 'label.carrying',
                'help' => 'help.carrying',
                'empty_data' => '0',
                'data' => (isset($options['data']) && $options['data']->getCarrying() !== null) ? $options['data']->getCarrying() : '0',
                'attr' => [
                    'placeholder' => 'label.carrying',
                    'title' => 'label.carrying',
                    'class' => 'form-control',
                    'name' => 'transport_carrying',
                    'min' => 0,
                    'max' => 99999999.99,
                    'step' => 0.01,
                ]
            ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'title.save',
            'attr' => [
                'class' => 'btn btn-primary',
            ]
        ]);

        $builder->add('saveAndCreateNew', SubmitType::class, [
            'label' => 'title.save_and_create_new',
            'attr' => [
                'class' => 'btn btn-primary',
            ]
        ]);

        $builder->add('saveAndStay', SubmitType::class, [
            'label' => 'title.save_and_stay',
            'attr' => [
                'class' => 'btn btn-primary',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transport::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Driver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DriverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('last_name', TextType::class, [
                'label' => 'label.last_name',
                'attr' => [
                    'placeholder' => 'label.last_name',
                    'title' => 'label.last_name',
                    'class' => 'form-control',
                    'name' => 'driver_last_name'
                ]
            ])
            ->add('first_name', TextType::class, [
                'label' => 'label.first_name',
                'attr' => [
                    'placeholder' => 'label.first_name',
                    'title' => 'label.first_name',
                    'class' => 'form-control',
                    'name' => 'driver_first_name'
                ]
            ])
            ->add('middle_name', TextType::class, [
                'label' => 'label.middle_name',
                'attr' => [
                    'placeholder' => 'label.middle_name',
                    'title' => 'label.middle_name',
                    'class' => 'form-control',
                    'name' => 'driver_middle_name'
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'label.phone',
                'help' => 'help.phone',
                'attr' => [
                    'placeholder' => 'label.phone',
                    'title' => 'label.phone',
                    'class' => 'form-control'
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
            'data_class' => Driver::class,
        ]);
    }
}

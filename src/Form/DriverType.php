<?php

namespace App\Form;

use App\Entity\Driver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class DriverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('last_name', TextType::class, [
                'label' => 'label.last_name',
                'attr' => [
                    'placeholder' => 'label.last_name',
                    'title' => 'label.last_name',
                    'class' => 'form-control',
                    'name' => 'driver_last_name'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('first_name', TextType::class, [
                'label' => 'label.first_name',
                'attr' => [
                    'placeholder' => 'label.first_name',
                    'title' => 'label.first_name',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('middle_name', TextType::class, [
                'label' => 'label.middle_name',
                'attr' => [
                    'placeholder' => 'label.middle_name',
                    'title' => 'label.middle_name',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'label.phone',
                'help' => 'help.phone',
                'attr' => [
                    'placeholder' => 'label.phone',
                    'title' => 'label.phone',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Driver::class,
        ]);
    }
}

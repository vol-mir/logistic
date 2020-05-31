<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('point_name', TextType::class, [
                'label' => 'label.point_name',
                'attr' => [
                    'placeholder' => 'label.point_name',
                    'title' => 'label.point_name',
                    'class' => 'form-control',
                    'name' => 'address_point_name'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('postcode', TextType::class, [
                'label' => 'label.postcode',
                'attr' => [
                    'placeholder' => 'label.postcode',
                    'title' => 'label.postcode',
                    'class' => 'form-control',
                    'name' => 'address_postcode'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'label.country',
                'attr' => [
                    'placeholder' => 'label.country',
                    'title' => 'label.country',
                    'class' => 'form-control',
                    'name' => 'address_country'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('region', TextType::class, [
                'label' => 'label.region',
                'attr' => [
                    'placeholder' => 'label.region',
                    'title' => 'label.region',
                    'class' => 'form-control',
                    'name' => 'address_region'
                ],
                'constraints' => [
                    new Length(['max' => 190]),
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'label.city',
                'attr' => [
                    'placeholder' => 'label.city',
                    'title' => 'label.city',
                    'class' => 'form-control',
                    'name' => 'address_city'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('locality', TextType::class, [
                'label' => 'label.locality',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.locality',
                    'title' => 'label.locality',
                    'class' => 'form-control',
                    'name' => 'address_locality'
                ],
                'constraints' => [
                    new Length(['max' => 190]),
                ]
            ])
            ->add('street', TextType::class, [
                'label' => 'label.street',
                'attr' => [
                    'placeholder' => 'label.street',
                    'title' => 'label.street',
                    'class' => 'form-control',
                    'name' => 'address_street'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}

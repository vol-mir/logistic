<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('point_name', TextType::class, [
                'label' => 'label.point_name',
                'attr' => [
                    'placeholder' => 'label.point_name',
                    'title' => 'label.point_name',
                    'class' => 'form-control',
                    'name' => 'address_point_name'
                ]
            ])
            ->add('postcode', TextType::class, [
                'label' => 'label.postcode',
                'attr' => [
                    'placeholder' => 'label.postcode',
                    'title' => 'label.postcode',
                    'class' => 'form-control',
                    'name' => 'address_postcode'
                ]
            ])
            ->add('country', TextType::class, [
                'label' => 'label.country',
                'attr' => [
                    'placeholder' => 'label.country',
                    'title' => 'label.country',
                    'class' => 'form-control',
                    'name' => 'address_country'
                ]
            ])
            ->add('region', TextType::class, [
                'label' => 'label.region',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.region',
                    'title' => 'label.region',
                    'class' => 'form-control',
                    'name' => 'address_region'
                ]
            ])
            ->add('city', TextType::class, [
                'label' => 'label.city',
                'attr' => [
                    'placeholder' => 'label.city',
                    'title' => 'label.city',
                    'class' => 'form-control',
                    'name' => 'address_city'
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
                ]
            ])
            ->add('street', TextType::class, [
                'label' => 'label.street',
                'attr' => [
                    'placeholder' => 'label.street',
                    'title' => 'label.street',
                    'class' => 'form-control',
                    'name' => 'address_street'
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
            'data_class' => Address::class,
        ]);
    }
}

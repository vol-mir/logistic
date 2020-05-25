<?php

namespace App\Form;

use App\Entity\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrganizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('registration_number', TextType::class, [
                'label' => 'label.registration_number',
                'attr' => [
                    'placeholder' => 'label.registration_number',
                    'title' => 'label.registration_number',
                    'class' => 'form-control',
                    'name' => 'organization_registration_number'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('abbreviated_name', TextType::class, [
                'label' => 'label.abbreviated_name',
                'help' => 'help.abbreviated_name',
                'attr' => [
                    'placeholder' => 'label.abbreviated_name',
                    'title' => 'label.abbreviated_name',
                    'class' => 'form-control',
                    'name' => 'organization_abbreviated_name'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('full_name', TextType::class, [
                'label' => 'label.full_name',
                'help' => 'help.full_name',
                'attr' => [
                    'placeholder' => 'label.full_name',
                    'title' => 'label.full_name',
                    'class' => 'form-control',
                    'name' => 'organization_full_name'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('base_contact_person', TextareaType::class, [
                'label' => 'label.base_contact_person',
                'attr' => [
                    'placeholder' => 'label.base_contact_person',
                    'title' => 'label.base_contact_person',
                    'class' => 'form-control ignore-validate',
                    'name' => 'organization_base_contact_person'
                ]
            ])
            ->add('base_working_hours', TextareaType::class, [
                'label' => 'label.base_working_hours',
                'attr' => [
                    'placeholder' => 'label.base_working_hours',
                    'title' => 'label.base_working_hours',
                    'class' => 'form-control ignore-validate',
                    'name' => 'organization_base_working_hours'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Organization::class,
        ]);
    }
}

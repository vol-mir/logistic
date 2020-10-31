<?php

namespace App\Form;

use App\Entity\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrganizationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('registration_number', TextType::class, [
                'label' => 'label.registration_number',
                'attr' => [
                    'placeholder' => 'label.registration_number',
                    'title' => 'label.registration_number',
                    'class' => 'form-control',
                    'name' => 'organization_registration_number'
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
                ]
            ])
            ->add('base_contact_person', TextareaType::class, [
                'label' => 'label.base_contact_person',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.base_contact_person',
                    'title' => 'label.base_contact_person',
                    'class' => 'form-control',
                    'name' => 'organization_base_contact_person'
                ]
            ])
            ->add('base_working_hours', TextareaType::class, [
                'label' => 'label.base_working_hours',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.base_working_hours',
                    'title' => 'label.base_working_hours',
                    'class' => 'form-control',
                    'name' => 'organization_base_working_hours'
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
            'data_class' => Organization::class,
        ]);
    }
}

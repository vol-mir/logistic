<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'label.username',
                'attr' => [
                    'placeholder' => 'label.username',
                    'title' => 'label.username',
                    'class' => 'form-control',
                    'name' => 'user_username'
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => array_flip(User::ROLES),
                'label' => 'label.roles',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.roles',
                    'title' => 'label.roles',
                    'class' => 'form-control select2',
                    'style' => 'width: 100%;',
                    'name' => 'user_roles'
                ]
            ])
            ->add('plain_password', PasswordType::class, [
                'label' => 'label.plain_password',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.plain_password',
                    'title' => 'label.plain_password',
                    'class' => 'form-control',
                    'name' => 'user_plain_password'
                ],
                'constraints' => [
                    new Assert\Length(['max' => 190]),
                ]
            ])
            ->add('first_name', TextType::class, [
                'label' => 'label.first_name',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.first_name',
                    'title' => 'label.first_name',
                    'class' => 'form-control',
                    'name' => 'user_first_name'
                ]
            ])
            ->add('last_name', TextType::class, [
                'label' => 'label.last_name',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.last_name',
                    'title' => 'label.last_name',
                    'class' => 'form-control',
                    'name' => 'user_last_name'
                ]
            ])
            ->add('middle_name', TextType::class, [
                'label' => 'label.middle_name',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.middle_name',
                    'title' => 'label.middle_name',
                    'class' => 'form-control',
                    'name' => 'user_middle_name'
                ]
            ])
            ->add('department', ChoiceType::class, [
                'choices' => array_flip(User::DEPARTMENTS),
                'label' => 'label.department',
                'attr' => [
                    'placeholder' => 'label.department',
                    'title' => 'label.department',
                    'class' => 'form-control',
                    'name' => 'user_department'
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
            'data_class' => User::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\TaskGoods;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class TaskGoodsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('goods', TextareaType::class, [
                'label' => 'label.goods',
                'attr' => [
                    'placeholder' => 'label.goods',
                    'title' => 'label.goods',
                    'class' => 'form-control',
                    'name' => 'task_goods_goods'
                ]
            ])
            ->add('weight', NumberType::class, [
                'label' => 'label.weight',
                'empty_data' => '0',
                'data' => (isset($options['data']) && $options['data']->getWeight() !== null) ? $options['data']->getWeight() : '0',
                'attr' => [
                    'placeholder' => 'label.weight',
                    'title' => 'label.weight',
                    'class' => 'form-control',
                    'name' => 'task_goods_weight',
                    'min'  => 0,
                    'max'  => 99999999,
                    'step' => 0.01,
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('unit', ChoiceType::class, [
                'choices' => array_flip(TaskGoods::LIST_UNITS),
                'label' => 'label.unit',
                'attr' => [
                    'placeholder' => 'label.unit',
                    'title' => 'label.unit',
                    'class' => 'form-control',
                    'name' => 'task_goods_unit'
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('dimensions', TextType::class, [
                'label' => 'label.dimensions',
                'help' => 'help.dimensions',
                'attr' => [
                    'placeholder' => 'label.dimensions',
                    'title' => 'label.dimensions',
                    'class' => 'form-control ignore-validate',
                    'name' => 'task_goods_dimensions'
                ],
            ])
            ->add('number_of_packages', NumberType::class, [
                'label' => 'label.number_of_packages',
                'empty_data' => '1',
                'data' => (isset($options['data']) && $options['data']->getNumberOfPackages() !== null) ? $options['data']->getNumberOfPackages() : '1',
                'attr' => [
                    'placeholder' => 'label.number_of_packages',
                    'title' => 'label.number_of_packages',
                    'class' => 'form-control',
                    'name' => 'task_goods_number_of_packages',
                    'min'  => 0,
                    'max'  => 99999999,
                    'step' => 1,
                ],
                'constraints' => [
                    new NotBlank(),
                    new PositiveOrZero(),
                ]
            ])
            ->add('loading_nature', ChoiceType::class, [
                'choices' => array_flip(TaskGoods::LIST_LOADING_NATURES),
                'label' => 'label.loading_nature',
                'attr' => [
                    'placeholder' => 'label.loading_nature',
                    'title' => 'label.loading_nature',
                    'class' => 'form-control',
                    'name' => 'task_goods_loading_nature'
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('contact_person', TextType::class, [
                'label' => 'label.contact_person',
                'help' => 'help.contact_person',
                'attr' => [
                    'placeholder' => 'label.contact_person',
                    'title' => 'label.contact_person',
                    'class' => 'form-control',
                    'name' => 'task_goods_contact_person'
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 190]),
                ]
            ])
            ->add('working_hours', TextType::class, [
                'label' => 'label.working_hours',
                'help' => 'help.working_hours',
                'attr' => [
                    'placeholder' => 'label.working_hours',
                    'title' => 'label.working_hours',
                    'class' => 'form-control',
                    'name' => 'task_goods_working_hours'
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
            'data_class' => TaskGoods::class,
        ]);
    }
}

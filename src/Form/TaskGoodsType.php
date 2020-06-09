<?php

namespace App\Form;

use App\Entity\TaskGoods;
use App\Entity\Organization;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
            ->add('note', TextareaType::class, [
                'label' => 'label.note',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.note',
                    'title' => 'label.note',
                    'class' => 'form-control',
                    'name' => 'task_goods_note'
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
                    new PositiveOrZero(),
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
                    new PositiveOrZero(),
                ]
            ])
            ->add('dimensions', TextType::class, [
                'label' => 'label.dimensions',
                'help' => 'help.dimensions',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.dimensions',
                    'title' => 'label.dimensions',
                    'class' => 'form-control',
                    'name' => 'task_goods_dimensions'
                ],
                'constraints' => [
                    new Length(['max' => 190]),
                ]

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
                    new PositiveOrZero(),
                ]
            ])
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'label' => 'label.organization',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('o')
                        ->orderBy('o.abbreviated_name', 'ASC');
                },
                'choice_label' => 'abbreviated_name',
                'choice_value' => 'id',
                'attr' => [
                    'placeholder' => 'label.organization',
                    'title' => 'label.organization',
                    'class' => 'form-control select2',
                    'style' => 'width: 100%;',
                    'name' => 'task_goods_organization'
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ])
        ;


        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {

                $form = $event->getForm();
                $data = $event->getData();

                $organization = $data->getOrganization();

                $base_contact_person = null === $data->getId() && null !== $organization ? $organization->getBaseContactPerson() : $data->getContactPerson();
                $base_working_hours = null === $data->getId() && null !== $organization ? $organization->getBaseWorkingHours() : $data->getWorkingHours();

                $form->add('contact_person', TextareaType::class, [
                    'label' => 'label.contact_person',
                    'data' => $base_contact_person,
                    'attr' => [
                        'placeholder' => 'label.contact_person',
                        'title' => 'label.contact_person',
                        'class' => 'form-control',
                        'name' => 'task_goods_contact_person'
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ]
                ]);

                $form->add('working_hours', TextareaType::class, [
                    'label' => 'label.working_hours',
                    'data' => $base_working_hours,
                    'attr' => [
                        'placeholder' => 'label.working_hours',
                        'title' => 'label.working_hours',
                        'class' => 'form-control',
                        'name' => 'task_goods_working_hours'
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ]
                ]);
            }
        );

        $builder->get('organization')->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event)  {
                $form = $event->getForm();

                $data = $event->getData();
                //dd($data);
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                //dd($event->getForm()->getData());

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                //$formModifier($event->getForm()->getParent(), $sport);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TaskGoods::class,
        ]);
    }
}

<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Organization;
use App\Entity\TaskGoods;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskGoodsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_task_goods', DateType::class, [
                'label' => 'label.date_task_goods',
                'widget' => 'single_text',
                'attr' => [
                    'placeholder' => 'label.date_task_goods',
                    'title' => 'label.date_task_goods',
                    'class' => 'form-control',
                    'name' => 'date_task_goods'
                ]
            ])
            ->add('goods', TextareaType::class, [
                'label' => 'label.goods',
                'attr' => [
                    'placeholder' => 'label.goods',
                    'title' => 'label.goods',
                    'class' => 'form-control',
                    'name' => 'task_goods_goods'
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
                    'min' => 0,
                    'max' => 99999999,
                    'step' => 0.01,
                ]
            ])
            ->add('unit', ChoiceType::class, [
                'choices' => array_flip(TaskGoods::LIST_UNITS),
                'label' => 'label.unit',
                'required' => false,
                'empty_data' => '1',
                'attr' => [
                    'placeholder' => 'label.unit',
                    'title' => 'label.unit',
                    'class' => 'form-control',
                    'name' => 'task_goods_unit'
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
                    'min' => 0,
                    'max' => 99999999,
                    'step' => 1,
                ]
            ])
            ->add('loading_nature', ChoiceType::class, [
                'choices' => array_flip(TaskGoods::LIST_LOADING_NATURES),
                'label' => 'label.loading_nature',
                'empty_data' => '1',
                'attr' => [
                    'title' => 'label.loading_nature',
                    'class' => 'form-control select2',
                    'style' => 'width: 100%;',
                    'name' => 'task_goods_loading_nature'
                ]
            ])
            ->add('organization', EntityType::class, [
                'class' => Organization::class,
                'label' => 'label.organization',
                'query_builder' => static function (EntityRepository $er) {
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
                ]
            ]);

        $formModifier = static function (FormInterface $form, Organization $organization = null, $dataContactPerson = null, $dataWorkingHours = null) {
            $form->add('address_office', EntityType::class, [
                'class' => Address::class,
                'label' => 'label.address_office',
                'required' => false,
                'placeholder' => 'placeholder.not_specified',
                'query_builder' => static function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('a')
                        ->where('a.organization = :organization')
                        ->orderBy('a.point_name', 'ASC')
                        ->addOrderBy('a.postcode', 'ASC')
                        ->addOrderBy('a.country', 'ASC')
                        ->addOrderBy('a.region', 'ASC')
                        ->addOrderBy('a.city', 'ASC')
                        ->addOrderBy('a.locality', 'ASC')
                        ->addOrderBy('a.street', 'ASC')
                        ->setParameter('organization', $organization);
                },
                'choice_label' => 'full_address',
                'choice_value' => 'id',
                'attr' => [
                    'title' => 'label.address_office',
                    'class' => 'form-control select2',
                    'style' => 'width: 100%;',
                    'name' => 'task_goods_address_office'
                ]
            ]);

            $form->add('address_goods_yard', EntityType::class, [
                'class' => Address::class,
                'label' => 'label.address_goods_yard',
                'query_builder' => static function (EntityRepository $er) use ($organization) {
                    return $er->createQueryBuilder('a')
                        ->where('a.organization = :organization')
                        ->orderBy('a.point_name', 'ASC')
                        ->addOrderBy('a.postcode', 'ASC')
                        ->addOrderBy('a.country', 'ASC')
                        ->addOrderBy('a.region', 'ASC')
                        ->addOrderBy('a.city', 'ASC')
                        ->addOrderBy('a.locality', 'ASC')
                        ->addOrderBy('a.street', 'ASC')
                        ->setParameter('organization', $organization);
                },
                'choice_label' => 'full_address',
                'choice_value' => 'id',
                'attr' => [
                    'placeholder' => 'label.address_goods_yard',
                    'title' => 'label.address_goods_yard',
                    'class' => 'form-control select2',
                    'style' => 'width: 100%;',
                    'name' => 'task_goods_address_goods_yard'
                ]
            ]);

            $form->add('contact_person', TextareaType::class, [
                'label' => 'label.contact_person',
                'data' => $dataContactPerson ?: ($organization ? $organization->getBaseContactPerson() : null),
                'attr' => [
                    'placeholder' => 'label.contact_person',
                    'title' => 'label.contact_person',
                    'class' => 'form-control',
                    'name' => 'task_goods_contact_person',
                ]
            ]);

            $form->add('working_hours', TextareaType::class, [
                'label' => 'label.working_hours',
                'data' => $dataWorkingHours ?: ($organization ? $organization->getBaseWorkingHours() : null),
                'attr' => [
                    'placeholder' => 'label.working_hours',
                    'title' => 'label.working_hours',
                    'class' => 'form-control',
                    'name' => 'task_goods_working_hours',
                ]
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event) use ($formModifier) {
                $data = $event->getData();
                $form = $event->getForm();
                $organization = $data->getOrganization();
                $formModifier($form, $organization, $data->getContactPerson(), $data->getWorkingHours());

                $status = $data->getStatus() ?: null;
                if ($status === null || $status === 1 || $status === 2) {
                    $form->add('status', ChoiceType::class, [
                        'choices' => array_flip(TaskGoods::BEGIN_STATUSES),
                        'label' => 'label.status',
                        'empty_data' => '1',
                        'attr' => [
                            'title' => 'label.status',
                            'class' => 'form-control select2',
                            'style' => 'width: 100%;',
                            'name' => 'task_goods_status'
                        ]
                    ]);
                }

            }
        );

        $builder->get('organization')->addEventListener(FormEvents::POST_SUBMIT,
            static function (FormEvent $event) use ($formModifier) {
                $organization = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $organization);
            }
        );

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
            'data_class' => TaskGoods::class,
        ]);
    }
}

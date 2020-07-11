<?php

namespace App\Form;

use App\Entity\Address;
use Psr\Log\LoggerInterface;
use App\Entity\TaskGoods;
use App\Entity\Driver;
use App\Entity\Transport;
use App\Entity\Organization;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Form\FormInterface;

class TaskGoodsFullType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('status', ChoiceType::class, [
                'choices' => array_flip(TaskGoods::STATUSES),
                'label' => 'label.status',
                'empty_data' => '1',
                'attr' => [
                    'title' => 'label.status',
                    'class' => 'form-control select2',
                    'style' => 'width: 100%;',
                    'name' => 'task_goods_status'
                ],
                'constraints' => [
                    new NotBlank(),
                    new PositiveOrZero(),
                ]
            ]);

        $builder->add('drivers', EntityType::class, [
                'class' => Driver::class,
                'label' => 'label.drivers',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('d')
                        ->orderBy('d.last_name', 'ASC')
                        ->addOrderBy('d.first_name', 'ASC')
                        ->addOrderBy('d.middle_name', 'ASC') ;
                },
                'choice_label' => 'full_name',
                'choice_value' => 'id',
                'multiple' => true,
                'required' => false,
                'attr' => [
                    'title' => 'label.drivers',
                    'class' => 'form-control select2',
                    'style' => 'width: 100%;',
                    'name' => 'task_goods_drivers'
                ]
            ]);

        $builder->add('transports', EntityType::class, [
            'class' => Transport::class,
            'label' => 'label.transports',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.marka', 'ASC')
                    ->addOrderBy('t.model', 'ASC')
                    ->addOrderBy('t.number', 'ASC') ;
            },
            'choice_label' => 'full_name',
            'choice_value' => 'id',
            'multiple' => true,
            'required' => false,
            'attr' => [
                'title' => 'label.transports',
                'class' => 'form-control select2',
                'style' => 'width: 100%;',
                'name' => 'task_goods_transports'
            ]
        ]);

        $builder->add('report', TextareaType::class, [
                'label' => 'label.report',
                'required' => false,
                'attr' => [
                    'placeholder' => 'label.report',
                    'title' => 'label.report',
                    'class' => 'form-control',
                    'name' => 'task_goods_report'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TaskGoods::class,
        ]);
    }
}

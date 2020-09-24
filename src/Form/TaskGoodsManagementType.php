<?php

namespace App\Form;

use App\Entity\Driver;
use App\Entity\TaskGoods;
use App\Entity\Transport;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class TaskGoodsManagementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            'query_builder' => static function (EntityRepository $er) {
                return $er->createQueryBuilder('d')
                    ->orderBy('d.last_name', 'ASC')
                    ->addOrderBy('d.first_name', 'ASC')
                    ->addOrderBy('d.middle_name', 'ASC');
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
            'query_builder' => static function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->orderBy('t.marka', 'ASC')
                    ->addOrderBy('t.model', 'ASC')
                    ->addOrderBy('t.number', 'ASC');
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

        $builder->add('save', SubmitType::class, [
            'label' => 'title.save',
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

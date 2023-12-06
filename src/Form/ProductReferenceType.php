<?php

namespace App\Form;

use App\Entity\ProductReference;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;

class ProductReferenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', Type\NumberType::class, [
                'constraints' => [
                    new Positive(),
                    new NotNull(),
                ]
            ])
            ->add('weight', Type\NumberType::class, [
                'constraints' => [
                    new Positive(),
                    new NotNull(),
                ]
            ])
            ->add('weightType', Type\TextType::class, [
                'constraints' => [
                    new NotNull(),
                    new NotBlank()
                ]
            ])
            ->add('submit', Type\SubmitType::class, [
                'label' => 'Ajouter'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductReference::class,
        ]);
    }
}

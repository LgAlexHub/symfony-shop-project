<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductCategory;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', Type\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 4])
                ]
            ])
            ->add('category', EntityType::class, [
                'class' => ProductCategory::class,
                
                'choices' => $options['categories'],
                'choice_label' => fn (ProductCategory $cat) => $cat->getLabel(),
                'choice_value' => fn (?ProductCategory $cat) => ($cat ? $cat->getId() : '')
            ], [
                'constraints' => [
                    new NotNull(),
                    // new Type(ProductCategory::class)
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
            'data_class' => Product::class,
            'categories' => []
        ]);

        $resolver->setAllowedTypes('categories', 'array');
    }
}

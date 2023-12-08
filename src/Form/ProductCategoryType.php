<?php

namespace App\Form;

use App\Entity\ProductCategory;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type as Type;

/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * This class represents the form type for handling product category data.
 */
class ProductCategoryType extends AbstractType
{
    /**
     * Builds the product category form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options for building the form.
     *
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', Type\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3])
                ]
            ])->add('submit', Type\SubmitType::class)
        ;
    }

    /**
     * Configures the default options for the form.
     *
     * @param OptionsResolver $resolver The options resolver.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductCategory::class,
        ]);
    }
}

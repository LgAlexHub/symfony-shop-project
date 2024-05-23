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
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * @author Aléki <alexlegras@hotmail.com>
 * @version 1
 * This class represents the form type for handling product reference data.
 */
class ProductReferenceType extends AbstractType
{

    /**
     * Builds the product reference form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options for building the form.
     *
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', Type\NumberType::class, [
                'constraints' => [
                    new Positive(message: "Le prix doit être une valeur positive"),
                    new NotNull(message: "Le prix doit être une valeur numérique valide"),
                ]
            ])
            ->add('weight', Type\NumberType::class, [
                'constraints' => [
                    new Positive(message: "Le poids doit être une valeur positive"),
                    new NotNull(message: "Le poids doit obligatoirement être renseigné"),
                ]
            ])
            ->add('weightType', Type\TextType::class, [
                'constraints' => [
                    new NotNull(message: "Le Type de poids doit être renseigné"),
                    new NotBlank(message: "Le Type de poids ne doit pas être vide")
                ]
            ])
            // ->add('imageFile', VichImageType::class)
            ->add('submit', Type\SubmitType::class, [
                'label' => 'Ajouter'
            ])
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
            'data_class' => ProductReference::class,
        ]);
    }
}

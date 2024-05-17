<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type as Type;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
* @author Aléki <alexlegras@hotmail.com>
* @version 1
* This class represents the form type for handling order data.
*/
class OrderType extends AbstractType
{
     /**
     * Builds the order form.
     *
     * @param FormBuilderInterface $builder The form builder.
     * @param array                $options The options for building the form.
     *
     * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('clientFirstName', Type\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3, 'max' => 100])
                ]
            ])
            ->add('clientLastName', Type\TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 3, 'max' => 100])
                ]
            ])
            ->add('email', Type\EmailType::class,  [
                'constraints' => [
                    new NotBlank()
                ]
            ])
            // utiliser pour faire passer l'item qui a déclenché la commande
            ->add('items', Type\HiddenType::class, ['mapped' => false])
            ->add('comment', Type\TextareaType::class)
            ->add('submit', Type\SubmitType::class, [
                'label' => 'Envoyer'
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
            'data_class' => Order::class,
            'allow_extra_fields' => true,
        ]);
    }
}

<?php

namespace App\Form;

// src/Form/IngredientFormType_v3.php
namespace App\Form;

use App\Entity\Ingredient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IngredientFormType_v3 extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr'  => ['class' => 'form-control'],
                'help'  => 'Le nom doit avoir 3 caractères ou plus.',
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'attr'  => ['class' => 'form-control'],
                'help'  => 'Entre 0 et 200.',
            ])
           ->add('submit', SubmitType::class, [
            'label' => $options['submit label'] ?? 'Créer l\'ingrédient', // valeur par défaut si non fournie
            'attr'  => ['class' => 'btn btn-primary mt-4'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'   => Ingredient::class,
            'submit label' => null, // option personnalisée demandée par la mission 16
        ]);
    }
}
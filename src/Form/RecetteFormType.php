<?php

namespace App\Form;

use App\Entity\Recette;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Ingredient;

class RecetteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('nom')
        ->add('temps')
        ->add('description')
        ->add('prix')
        ->add('difficulte')
        ->add('ingredients', EntityType::class, [
            'class' => Ingredient::class,
            'choice_label' => 'nom',
            'multiple' => true,       // autorise plusieurs ingrédients
            'expanded' => true,       // checkbox
        ])
    ;
}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}

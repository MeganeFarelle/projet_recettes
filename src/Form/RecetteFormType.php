<?php

namespace App\Form;

use App\Entity\Recette;
use App\Entity\Ingredient;
use App\Form\TagType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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

            // INGREDIENTS : tu avais déjà ça, je laisse
            ->add('ingredients', EntityType::class, [
                'class' => Ingredient::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
            ])

            // ⭐⭐⭐ TAGS : Form Collection (MISSION 41)
            ->add('tags', CollectionType::class, [
                'entry_type' => TagType::class, // formulaire d’un tag
                'allow_add' => true,            // bouton "Ajouter"
                'allow_delete' => true,         // bouton "Supprimer"
                'by_reference' => false,        // indispensable pour ManyToMany
                'prototype' => true,            // nécessaire pour JS
                'label' => 'Tags',
                'entry_options' => [
                    'label' => false
                ],
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

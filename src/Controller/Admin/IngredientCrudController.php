<?php

namespace App\Controller\Admin;

use App\Entity\Ingredient;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class IngredientCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Ingredient::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [

            // ID affiché seulement en liste
            IdField::new('id')->hideOnForm(),

            // Nom de l’ingrédient
            TextField::new('nom'),

            // Prix
            MoneyField::new('prix')
                ->setCurrency('EUR'),

            // Dates non modifiables
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),

            // Liste des recettes liées
            AssociationField::new('recettes')
                ->formatValue(function ($value, $ingredient) {

                    $recettes = $ingredient->getRecettes();
                    if ($recettes->isEmpty()) {
                        return 'Aucune recette';
                    }

                    $label = '';
                    foreach ($recettes as $recette) {
                        $label .= $recette->getNom() . ' (#' . $recette->getId() . '), ';
                    }

                    return rtrim($label, ', ');
                })
                ->onlyOnIndex(), // affiché seulement dans la liste
        ];
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Recette;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;

class RecetteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recette::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [

            // ID pas éditable
            IdField::new('id')->hideOnForm(),

            // Nom de la recette
            TextField::new('nom'),

            // Description
            TextEditorField::new('description')->hideOnIndex(),

            // Temps (minutes)
            IntegerField::new('temps'),

            // Prix
            MoneyField::new('prix')->setCurrency('EUR'),

            // Difficulté
            IntegerField::new('difficulte'),

            // Dates non modifiables
            DateTimeField::new('createdAt')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),

            // INGREDIENTS : affichage personnalisé (MISSION 39)
            AssociationField::new('ingredients')
                ->formatValue(function ($value, $recette) {

                    $ingredients = $recette->getIngredients();

                    if ($ingredients->isEmpty()) {
                        return 'Aucun ingrédient';
                    }

                    $label = '';

                    foreach ($ingredients as $ingredient) {
                        $label .= $ingredient->getNom()
                                . ' (#' . $ingredient->getId() . '), ';
                    }

                    return rtrim($label, ', ');
                })
                ->onlyOnIndex(), // affichage LISTE (index)

            // Champ standard pour le formulaire (ajout / edit)
            AssociationField::new('ingredients')->onlyOnForms(),

        ];
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;


class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [

            // ❌ ID - jamais modifiable
            IdField::new('id')->hideOnForm(),

            // 📧 Email
            EmailField::new('email'),

            // 👤 Nom & prénom
            TextField::new('nom'),
            TextField::new('prenom'),

            // 📌 Rôles (ROLE_USER, ROLE_ADMIN)
            ArrayField::new('roles'),

            // ❌ Ne jamais afficher / éditer le password en clair
            TextField::new('password')->hideOnIndex()->hideOnForm(),

            // 🌍 Adresse
            TextField::new('ville')->hideOnIndex(),
            TextField::new('cp')->hideOnIndex(),

            // ✔️ Email vérifié ?
            BooleanField::new('isVerified')->onlyOnIndex(),

            // 🥘 Toutes les recettes de cet user (pas modifiable)
            AssociationField::new('recettes')
                ->formatValue(function ($value, $user) {

                    $recettes = $user->getRecettes();

                    if ($recettes->isEmpty()) {
                        return 'Aucune recette';
                    }

                    $label = '';

                    foreach ($recettes as $recette) {
                        $label .= $recette->getNom() . ' (#' . $recette->getId() . '), ';
                    }

                    return rtrim($label, ', ');
                })
                ->onlyOnIndex(),

            // Formulaire pour associer des recettes (si besoin)
            AssociationField::new('recettes')->onlyOnForms(),

        ];
    }
}

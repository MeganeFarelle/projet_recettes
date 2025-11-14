<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recette;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // ---------------------------------------
        // 1) Générer des Ingrédients
        // ---------------------------------------
        $ingredients = [];

        for ($i = 0; $i < 50; $i++) {
            $ingredient = new Ingredient();
            $ingredient->setNom($faker->word());
            $ingredient->setPrix($faker->randomFloat(2, 0, 200));
            $ingredient->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($ingredient);
            $ingredients[] = $ingredient; // important pour les recettes
        }

        // ---------------------------------------
        // 2) Générer des Recettes
        // ---------------------------------------
        for ($i = 0; $i < 20; $i++) {
            $recette = new Recette();
            $recette->setNom($faker->sentence(3));
            $recette->setTemps($faker->numberBetween(5, 120));
            $recette->setDescription($faker->paragraph());
            $recette->setPrix($faker->randomFloat(2, 0, 200));
            $recette->setDifficulte($faker->numberBetween(0, 5));

            // 3) Ajouter ENTRE 2 ET 8 ingrédients
            $nbIngredients = $faker->numberBetween(2, 8);
            $selection = $faker->randomElements($ingredients, $nbIngredients);

            foreach ($selection as $ingredient) {
                $recette->addIngredient($ingredient);
            }

            $manager->persist($recette);
        }

        // ---------------------------------------
        // 4) Envoyer le tout en BDD
        // ---------------------------------------
        $manager->flush();
    }
}

<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // ----------------------------------------------------
        // 1) Générer les utilisateurs
        // ----------------------------------------------------

        // ADMIN
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $admin->setNom('Admin');
        $admin->setPrenom('Super');
        $admin->setVille('Paris');
        $admin->setCp('75000');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            $this->passwordHasher->hashPassword($admin, 'password')
        );
        $manager->persist($admin);

        // 20 utilisateurs simples
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->email());
            $user->setNom($faker->lastName());
            $user->setPrenom($faker->firstName());
            $user->setVille($faker->city());
            $user->setCp($faker->postcode());
            $user->setRoles(['ROLE_USER']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'password')
            );

            $manager->persist($user);
        }

        // ----------------------------------------------------
        // 2) Générer des Ingrédients
        // ----------------------------------------------------
        $ingredients = [];

        for ($i = 0; $i < 50; $i++) {
            $ingredient = new Ingredient();
            $ingredient->setNom($faker->word());
            $ingredient->setPrix($faker->randomFloat(2, 0, 200));
            $ingredient->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($ingredient);
            $ingredients[] = $ingredient; // utile pour les recettes
        }

        // ----------------------------------------------------
        // 3) Générer des Recettes
        // ----------------------------------------------------
        for ($i = 0; $i < 20; $i++) {
            $recette = new Recette();
            $recette->setNom($faker->sentence(3));
            $recette->setTemps($faker->numberBetween(5, 120));
            $recette->setDescription($faker->paragraph());
            $recette->setPrix($faker->randomFloat(2, 0, 200));
            $recette->setDifficulte($faker->numberBetween(0, 5));

            // Ajouter entre 2 et 8 ingrédients
            $nbIngredients = $faker->numberBetween(2, 8);
            $selection = $faker->randomElements($ingredients, $nbIngredients);

            foreach ($selection as $ingredient) {
                $recette->addIngredient($ingredient);
            }

            $manager->persist($recette);
        }

        // ----------------------------------------------------
        // 4) Flush final
        // ----------------------------------------------------
        $manager->flush();
    }
}

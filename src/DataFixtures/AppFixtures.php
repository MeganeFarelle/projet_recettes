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
        // 1) Create ADMIN
        // ----------------------------------------------------
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

        // ----------------------------------------------------
        // 2) Create 20 normal users
        // ----------------------------------------------------
        $users = [];
        $users[] = $admin;     // include admin in the list for recipes

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
            $users[] = $user;
        }

        // ----------------------------------------------------
        // IMPORTANT: flush users before creating recettes!
        // ----------------------------------------------------
        $manager->flush();

        // ----------------------------------------------------
        // 3) Create 50 Ingredients
        // ----------------------------------------------------
        $ingredients = [];

        for ($i = 0; $i < 50; $i++) {
            $ingredient = new Ingredient();
            $ingredient->setNom("Ingredient_" . uniqid()); // UNIQUE NAME → fixes slug errors
            $ingredient->setPrix($faker->randomFloat(2, 0, 200));
            $ingredient->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($ingredient);
            $ingredients[] = $ingredient;
        }

        // ----------------------------------------------------
        // 4) Create 20 Recipes
        // ----------------------------------------------------
        for ($i = 0; $i < 20; $i++) {
            $recette = new Recette();
            $recette->setNom($faker->sentence(3));
            $recette->setTemps($faker->numberBetween(5, 120));
            $recette->setDescription($faker->paragraph());
            $recette->setPrix($faker->randomFloat(2, 0, 200));
            $recette->setDifficulte($faker->numberBetween(0, 5));

            // ➜ Assign RECIPE to a RANDOM USER
            $recette->setUser($faker->randomElement($users));

            // Add ingredients
            $nbIngredients = $faker->numberBetween(2, 8);
            $selection = $faker->randomElements($ingredients, $nbIngredients);

            foreach ($selection as $ingredient) {
                $recette->addIngredient($ingredient);
            }

            $manager->persist($recette);
        }

        // ----------------------------------------------------
        // 5) Final flush
        // ----------------------------------------------------
        $manager->flush();
    }
}

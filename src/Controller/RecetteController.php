<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteFormType;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecetteController extends AbstractController
{
    #[Route('/recette', name: 'recette.index')]
    public function index(RecetteRepository $recetteRepository): Response
    {
        $recettes = $recetteRepository->findAll();

        return $this->render('recette/index.html.twig', [
            'recettes' => $recettes,
            'title'    => 'Toutes les recettes avec leurs ingrédients',
        ]);
    }

    #[Route('/recette/ingredient', name: 'recette.ingredient_dql')]
    public function recetteIngredientDql(RecetteRepository $recetteRepository): Response
    {
        $recettes = $recetteRepository->find_recette_ingredient_dql();

        return $this->render('recette/index.html.twig', [
            'recettes' => $recettes,
            'title'    => '10 premières recettes (DQL)',
        ]);
    }

    #[Route('/recette/avec_5_ingredients', name: 'recette.avec_5_ingredients_dql')]
    public function recetteAvec5IngredientsDql(RecetteRepository $recetteRepository): Response
    {
        $recettes = $recetteRepository->find_recette_avec_5_ingredients_dql();

        return $this->render('recette/index.html.twig', [
            'recettes' => $recettes,
            'title'    => 'Recettes avec exactement 5 ingrédients (DQL)',
        ]);
    }

    #[Route('/recette/ingredient_sql', name: 'recette.ingredient_sql')]
    public function recetteIngredientSql(RecetteRepository $recetteRepository): Response
    {
        $rows = $recetteRepository->find_recette_ingredient_sql();

        return $this->render('recette/ingredient_sql.html.twig', [
            'rows'  => $rows,
            'title' => '10 premières recettes + ingrédients (SQL)',
        ]);
    }

    #[Route('/recette/avec_5_ingredients_sql', name: 'recette.avec_5_ingredients_sql')]
    public function recetteAvec5IngredientsSql(RecetteRepository $recetteRepository): Response
    {
        $rows = $recetteRepository->find_recette_avec_5_ingredients_sql();

        return $this->render('recette/avec_5_ingredients_sql.html.twig', [
            'rows'  => $rows,
            'title' => 'Recettes avec 5 ingrédients (SQL)',
        ]);
    }

    #[Route('/recette/create', name: 'recette.create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $recette = new Recette();

        $form = $this->createForm(RecetteFormType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // ⭐⭐⭐ IMPORTANT POUR ÉVITER L'ERREUR user_id NULL ⭐⭐⭐
            $recette->setUser($this->getUser());

            // Vérifier minimum 3 ingrédients
            if (count($recette->getIngredients()) < 3) {
                $this->addFlash('error', 'Vous devez choisir au moins 3 ingrédients.');
            } else {
                $em->persist($recette);
                $em->flush();

                $this->addFlash('success', 'Recette créée avec succès !');

                return $this->redirectToRoute('recette.index');
            }
        }

        return $this->render('recette/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

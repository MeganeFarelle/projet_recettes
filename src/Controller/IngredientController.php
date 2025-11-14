<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientFormType;
use App\Form\IngredientFormType_v3;
use App\Repository\IngredientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class IngredientController extends AbstractController
{
    #[Route('/ingredient', name: 'app_ingredient')]
    public function index(IngredientRepository $ingredient_repository): Response
    {
        $ingredients = $ingredient_repository->findAll();

        return $this->render('ingredient/index.html.twig', [
            'ingredients' => $ingredients,
        ]);
    }

   #[Route('/ingredient/greater_than_100', name: 'app_ingredient_gt_100')]
public function index_only_greater_than_100(IngredientRepository $ingredient_repository): Response
{
    $ingredients = $ingredient_repository->findAll();

    $ingredients_100 = [];
    foreach ($ingredients as $ingredient) {
        if ($ingredient->getPrix() > 100) {
            $ingredients_100[] = $ingredient;
        }
    }

    // on réutilise la vue index.html.twig
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $ingredients_100,
    ]);
}

#[Route('/ingredient/greater_than_100_v2', name: 'app_ingredient_gt_100_v2')]
public function index_only_greater_than_100_v2(IngredientRepository $ingredient_repository): Response
{
    // findAll() retourne un tableau d’objets Ingredient
    $ingredients = $ingredient_repository->findAll();

    // On convertit ce tableau en Collection
    $collection = new ArrayCollection($ingredients);

    // On filtre avec une closure : garde uniquement ceux dont le prix > 100
    $ingredients_100 = $collection->filter(function($ingredient) {
        return $ingredient->getPrix() > 100;
    });
    return $this->render('ingredient/index.html.twig',  ['ingredients' => $ingredients_100]);
}

#[Route('/ingredient/greater_than_100_v3', name: 'app_ingredient_gt_100_v3')]
public function index_only_greater_than_100_v3(IngredientRepository $ingredient_repository): Response
{
    $ingredients = new ArrayCollection($ingredient_repository->findAll());

    $criteria = Criteria::create()->where(Criteria::expr()->gt("prix", 100));

    $ingredients_100 = $ingredients->matching($criteria);

    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $ingredients_100,
    ]);
}

#[Route('/ingredient/greater_than_100_v4', name: 'app_ingredient_gt_100_v4')]
public function index_only_greater_than_100_v4(IngredientRepository $ingredientRepository): Response
{
    // Définir le critère : prix > 100
    $criteria = Criteria::create()->where(Criteria::expr()->gt('prix', 100));

    // Appliquer le critère directement au repository
    $ingredients = $ingredientRepository->matching($criteria);

    // Envoyer à la vue
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $ingredients,
    ]);
}
#[Route('/ingredient/create', name: 'ingredient.create', methods: ['GET'])]
    public function create(): Response
    {
        $crea_form = $this->createFormBuilder()
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('prix', NumberType::class, ['label' => 'Prix (EUR)'])
            ->add('save', SubmitType::class, ['label' => 'Créer'])
            ->setAction($this->generateUrl('ingredient.store')) 
            ->setMethod('POST')
            ->getForm();

        return $this->render('ingredient/create.html.twig', [
            'crea_form' => $crea_form->createView(),
        ]);
    }

    #[Route('/ingredient/store', name: 'ingredient.store', methods: ['POST'])]
public function store(Request $request, EntityManagerInterface $em): Response
{
    $ingredient = new Ingredient();

    $data = $request->request->all();
    //dd($data);
    $formData = $data['form'] ?? [];
    $ingredient->setNom($formData['nom'] ?? '');
    $ingredient->setPrix((float) ($formData['prix'] ?? 0));
    if (method_exists($ingredient, 'setCreatedAt')) {
        $ingredient->setCreatedAt(new \DateTimeImmutable());
    }
    $em->persist($ingredient);
    $em->flush();
    
    $this->addFlash('success', 'Votre ingrédient a bien été créé avec succès !');
    return $this->redirectToRoute('app_ingredient');
}

    #[Route('/ingredient/create_and_store', name: 'ingredient.create_and_store', methods: ['GET','POST'])]
    public function createAndStore(Request $request, EntityManagerInterface $em): Response
    {
        // 4) Créer l’objet à persister
        $ingredient = new Ingredient();

        // 5) Construire le formulaire SANS binder l’objet (pour que getData() renvoie un tableau)
        $crea_form = $this->createFormBuilder()
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('prix', NumberType::class, ['label' => 'Prix (EUR)'])
            ->add('save', SubmitType::class, ['label' => 'Créer'])
            ->getForm();

        // 6) Hydrater le form avec la requête (et savoir s’il est soumis)
        $crea_form->handleRequest($request);

        // 7) Test formulaire soumis ET valide
        if ($crea_form->isSubmitted() && $crea_form->isValid()) {
            // 8–9) Récupérer les données + (option) dd pour voir la structure
            $data = $crea_form->getData();
            
            // dd($data); // décommente une fois pour regarder puis re-commente

            // 10) Reporter les valeurs sur l’objet
            $ingredient->setNom($data['nom'] ?? '');
            $ingredient->setPrix((float) ($data['prix'] ?? 0));

            // 14) La date de création n’est PAS dans le form : on la met ici
            $ingredient->setCreatedAt(new \DateTimeImmutable());

            // 11) Sauvegarder
            $em->persist($ingredient);
            $em->flush();

            // 12) Redirection vers la liste
            $this->addFlash('success', 'Ingrédient créé avec succès.');
            return $this->redirectToRoute('app_ingredient');
        }

        // Si pas soumis / pas valide → réafficher le formulaire
        return $this->render('ingredient/create.html.twig', [
            'crea_form' => $crea_form->createView(),
        ]);
    }

    #[Route('/ingredient/create_and_store_v2', name: 'ingredient.create_and_store', methods: ['GET','POST'])]
    public function createAndStoreV2(Request $request, EntityManagerInterface $em): Response
    {
        // 4) Créer l’objet à persister
        $ingredient = new Ingredient();

        // 5) Construire le formulaire SANS binder l’objet (pour que getData() renvoie un tableau)
        $crea_form = $this->createForm(IngredientFormType ::class); 

        // 6) Hydrater le form avec la requête (et savoir s’il est soumis)
        $crea_form->handleRequest($request);

        // 7) Test formulaire soumis ET valide
        if ($crea_form->isSubmitted() && $crea_form->isValid()) {
            // 8–9) Récupérer les données + (option) dd pour voir la structure
            $data = $crea_form->getData();
            
            // dd($data); // décommente une fois pour regarder puis re-commente

            // 10) Reporter les valeurs sur l’objet
            $ingredient->setNom($data['nom'] ?? '');
            $ingredient->setPrix((float) ($data['prix']));

            // 14) La date de création n’est PAS dans le form : on la met ici
            $ingredient->setCreatedAt(new \DateTimeImmutable());

            // 11) Sauvegarder
            $em->persist($ingredient);
            $em->flush();

            // 12) Redirection vers la liste
            $this->addFlash('success', 'Ingrédient créé avec succès.');
            return $this->redirectToRoute('app_ingredient');
        }

        // Si pas soumis / pas valide → réafficher le formulaire
        return $this->render('ingredient/create.html.twig', [
            'crea_form' => $crea_form->createView(),
        ]);
    }

#[Route('/ingredient/create_and_store_v3', name: 'ingredient.create_and_store_v3', methods: ['GET','POST'])]
public function create_and_store_v3(Request $request, EntityManagerInterface $em): Response
{
    $ingredient = new Ingredient();
    $form = $this->createForm(IngredientFormType_v3::class, $ingredient);
    $form->handleRequest($request);
    $ingredient->setCreatedAt(new \DateTimeImmutable());
    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($ingredient);
        $em->flush();
        $this->addFlash('success', 'Ingrédient créé !');
        return $this->redirectToRoute('app_ingredient');
    }

    return $this->render('ingredient/create_v3.html.twig', [
        'crea_form' => $form->createView(),
    ]);
}

#[Route('/ingredient/edit/{id}', name: 'ingredient.edit', methods: ['GET','PUT'])]
public function edit(
    int $id,
    Request $request,
    IngredientRepository $repo,
    EntityManagerInterface $em
): Response {
    $ingredient = $repo->find($id);
    if (!$ingredient) {
        throw $this->createNotFoundException('Ingrédient introuvable.');
    }

    $form = $this->createForm(IngredientFormType_v3::class, $ingredient, [
        'method'       => 'PUT',
        'submit label' => 'Enregistrer les modifications',
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->flush();
        $this->addFlash('success', 'Votre ingrédient a été modifié avec succès !');
        return $this->redirectToRoute('app_ingredient');
    }

    return $this->render('ingredient/create_v3.html.twig', [
        'crea_form' => $form->createView(),
        'title'     => 'Modifier un ingrédient',
    ]);
}

#[Route('/ingredient/delete/{id}', name: 'ingredient.delete', methods: ['DELETE'])]
public function delete(int $id, IngredientRepository $repo, EntityManagerInterface $em): Response
{
    $ingredient = $repo->find($id);
    $em->remove($ingredient); 
    $em->flush(); 

    $this->addFlash('success', 'Votre ingrédient a été supprimé avec succès !');
    return $this->redirectToRoute('app_ingredient');
}

#[Route('/ingredient/tomate', name: 'ingredient.tomate')]
public function index_ingredient_tomate(IngredientRepository $repository): Response
{
    $ingredients = $repository->find_ingredient_tomate();

    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $ingredients,
    ]);
}

#[Route('/ingredient/tomate_5', name: 'ingredient.tomate_5')]
public function index_ingredient_tomate_5(IngredientRepository $repository): Response
{
    $ingredients = $repository->find_ingredient_tomate_5();

    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $ingredients,
    ]);
}

#[Route('/ingredient/tom', name: 'ingredient.tom')]
public function index_ingredient_tom(IngredientRepository $repository): Response
{
    $ingredients = $repository->find_ingredient_tom();

    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $ingredients,
    ]);
}

#[Route('/ingredient/by_price/{price}', name: 'ingredient.by_price')]
public function index_ingredient_by_price(
    int $price,
    IngredientRepository $repository
): Response {
    $ingredients = $repository->find_ingredient_by_price($price);

    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $ingredients,
    ]);
}

#[Route(
    '/ingredient/by_price/{price}/by_name/{name}',
    name: 'ingredient.by_price_and_name'
)]
public function index_ingredient_by_price_and_name(
    int $price,
    string $name,
    IngredientRepository $repository
): Response {
    $ingredients = $repository->find_ingredient_by_price_and_name($price, $name);

    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $ingredients,
    ]);
}

#[Route('/ingredient/sql', name: 'ingredient.index_sql')]
public function index_sql(IngredientRepository $repository): Response
{
    $ingredients = $repository->findAll_sql();

    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $ingredients,
    ]);
}

#[Route('/ingredient/dql', name: 'ingredient.index_dql')]
public function index_dql(IngredientRepository $repository): Response
{
    $ingredients = $repository->findAll_dql();

    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $ingredients,
    ]);
}

#[Route('/ingredient/tomate_sql', name: 'ingredient.tomate_sql')]
public function index_ingredient_tomate_sql(IngredientRepository $repo): Response
{
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $repo->find_ingredient_tomate_sql(),
    ]);
}

#[Route('/ingredient/tomate_5_sql', name: 'ingredient.tomate_5_sql')]
public function index_ingredient_tomate_5_sql(IngredientRepository $repo): Response
{
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $repo->find_ingredient_tomate_5_sql(),
    ]);
}

#[Route('/ingredient/tom_sql', name: 'ingredient.tom_sql')]
public function index_ingredient_tom_sql(IngredientRepository $repo): Response
{
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $repo->find_ingredient_tom_sql(),
    ]);
}

#[Route('/ingredient/by_price_sql/{price}', name: 'ingredient.by_price_sql')]
public function index_ingredient_by_price_sql(IngredientRepository $repo, int $price): Response
{
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $repo->find_ingredient_by_price_sql($price),
    ]);
}

#[Route('/ingredient/by_price_sql/{price}/by_name_sql/{name}', name: 'ingredient.by_price_and_name_sql')]
public function index_ingredient_by_price_and_name_sql(IngredientRepository $repo, int $price, string $name): Response
{
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $repo->find_ingredient_by_price_and_name_sql($price, $name),
    ]);
}

#[Route('/ingredient/tomate_dql', name: 'ingredient.tomate_dql')]
public function index_ingredient_tomate_dql(IngredientRepository $repo): Response
{
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $repo->find_ingredient_tomate_dql(),
    ]);
}

#[Route('/ingredient/tomate_5_dql', name: 'ingredient.tomate_5_dql')]
public function index_ingredient_tomate_5_dql(IngredientRepository $repo): Response
{
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $repo->find_ingredient_tomate_5_dql(),
    ]);
}

#[Route('/ingredient/tom_dql', name: 'ingredient.tom_dql')]
public function index_ingredient_tom_dql(IngredientRepository $repo): Response
{
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $repo->find_ingredient_tom_dql(),
    ]);
}

#[Route('/ingredient/by_price_dql/{price}', name: 'ingredient.by_price_dql')]
public function index_ingredient_by_price_dql(IngredientRepository $repo, int $price): Response
{
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $repo->find_ingredient_by_price_dql($price),
    ]);
}

#[Route('/ingredient/by_price_dql/{price}/by_name_dql/{name}', name: 'ingredient.by_price_and_name_dql')]
public function index_ingredient_by_price_and_name_dql(IngredientRepository $repo, int $price, string $name): Response
{
    return $this->render('ingredient/index.html.twig', [
        'ingredients' => $repo->find_ingredient_by_price_and_name_dql($price, $name),
    ]);
}

}




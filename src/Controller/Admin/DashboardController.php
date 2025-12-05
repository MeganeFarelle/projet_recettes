<?php

namespace App\Controller\Admin;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Option 3 : on utilise un template personnalisé.
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration des Recettes')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // CRUD Recettes
        yield MenuItem::linkToCrud('Recettes', 'fas fa-book', Recette::class);

        // CRUD Ingrédients
        yield MenuItem::linkToCrud('Ingrédients', 'fas fa-leaf', Ingredient::class);

        // CRUD Utilisateurs
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', User::class);
    }
}
<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    public function index(): Response
    {
        return $this->redirect(
            $this->adminUrlGenerator->setController(AnimeCrudController::class)->generateUrl()
        );
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Anime Tracker - Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Accueil', 'fa fa-home');

        yield MenuItem::section('Catalogue');
        yield MenuItem::linkToRoute('Animes', 'fa fa-video', 'admin_anime_index');
        yield MenuItem::linkToRoute('Studios', 'fa fa-building', 'admin_studio_index');
        yield MenuItem::linkToRoute('Genres', 'fa fa-tags', 'admin_genre_index');

        yield MenuItem::section('Communauté');
        yield MenuItem::linkToRoute('Avis / Reviews', 'fa fa-star', 'admin_review_index');
        yield MenuItem::linkToRoute('Suivis (Tracker)', 'fa fa-bookmark', 'admin_tracker_index');

        yield MenuItem::section('Administration');
        yield MenuItem::linkToRoute('Utilisateurs', 'fa fa-users', 'admin_user_index');
    }
}
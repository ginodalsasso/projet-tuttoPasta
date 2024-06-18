<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\DayOff;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Project;
use App\Entity\Service;
use App\Entity\Category;
use App\Entity\ProjectImg;
use App\Entity\Appointment;
use App\Entity\Administration;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(AdministrationCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Projet TuttoPasta');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Catégories', 'fas fa-th-list', Category::class);
        yield MenuItem::linkToCrud('Administration', 'fas fa-cogs', Administration::class);
        yield MenuItem::linkToCrud('Projets', 'fas fa-project-diagram', Project::class);
        yield MenuItem::linkToCrud('Images projets', 'fas fa-images', ProjectImg::class);
        yield MenuItem::linkToCrud('Services', 'fas fa-concierge-bell', Service::class);
        yield MenuItem::linkToCrud('Articles', 'fas fa-newspaper', Article::class);
        yield MenuItem::linkToCrud('Commentaires', 'fas fa-comments', Comment::class);
        
        yield MenuItem::linkToCrud('Rendez-vous', 'fas fa-calendar-check', Appointment::class);
        yield MenuItem::linkToCrud('Jours de congés', 'fas fa-calendar-alt', DayOff::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
    }
}

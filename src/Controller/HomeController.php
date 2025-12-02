<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Dashboard\DashboardBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    #[Route('/', name: 'home')]
    #[Route('/home', name: 'dashboard')]
    public function index(DashboardBuilder $dashboardBuilder): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }
        $context = $dashboardBuilder->build($user);

        return $this->render('home/index.html.twig', [
            'dashboards' => $context->dashboards(),
            'is_admin' => $context->isAdmin(),
        ]);
    }
}
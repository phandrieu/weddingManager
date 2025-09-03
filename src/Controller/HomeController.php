<?php

namespace App\Controller;

use App\Repository\WeddingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function index(WeddingRepository $weddingRepository): Response
    {
        // Récupère tous les mariages
        $weddings = $weddingRepository->findAll();

        return $this->render('home/index.html.twig', [
            'weddings' => $weddings,
        ]);
    }
    #[Route('/', name: 'public')]
    public function public(WeddingRepository $weddingRepository): Response
    {
        // Récupère tous les mariages
        $weddings = $weddingRepository->findAll();

        return $this->render('home/public.html.twig', [
            
        ]);
    }
}
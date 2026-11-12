<?php

namespace App\Controller;

use App\Repository\AnimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AnimeRepository $animes): Response
    {
        return $this->render('home/index.html.twig', [
            'featured' => $animes->findBy(['is_featured' => true, 'deleted_at' => null], ['created_at' => 'DESC'], 6),
            'latest' => $animes->findBy(['deleted_at' => null], ['created_at' => 'DESC'], 10),
        ]);
    }
}

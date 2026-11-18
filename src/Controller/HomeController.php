<?php

namespace App\Controller;

use App\Repository\AnimeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    // Accueil : les 10 derniers animes ajoutés
    #[Route('/', name: 'app_home')]
    public function index(AnimeRepository $animes): Response
    {
        return $this->render('home/index.html.twig', [
            'latest' => $animes->findBy(['deleted_at' => null], ['created_at' => 'DESC'], 10),
        ]);
    }
}

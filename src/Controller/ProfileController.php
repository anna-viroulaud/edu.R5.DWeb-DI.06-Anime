<?php

namespace App\Controller;

use App\Entity\Tracker;
use App\Repository\TrackerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function index(TrackerRepository $trackers): Response
    {
        return $this->render('profile/index.html.twig', [
            'trackers' => $trackers->findBy(['user' => $this->getUser()], ['updatedAt' => 'DESC']),
            'statuses' => Tracker::STATUSES,
        ]);
    }
}

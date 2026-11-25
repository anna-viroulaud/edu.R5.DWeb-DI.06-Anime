<?php

namespace App\Controller;

use App\Entity\Anime;
use App\Entity\Review;
use App\Entity\Tracker;
use App\Form\AnimeType;
use App\Repository\AnimeRepository;
use App\Repository\ReviewRepository;
use App\Repository\TrackerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnimeController extends AbstractController
{
    // Catalogue : liste de tous les animes (non supprimés), triés par titre
    #[Route('/animes', name: 'app_anime_index')]
    public function index(AnimeRepository $animes): Response
    {
        return $this->render('anime/index.html.twig', [
            'animes' => $animes->findBy(['deleted_at' => null], ['title' => 'ASC']),
        ]);
    }

    #[Route('/animes/{id}', name: 'app_anime_show', requirements: ['id' => '\d+'])]
    public function show(Anime $anime, ReviewRepository $reviews, TrackerRepository $trackers): Response
    {
        if ($anime->getDeletedAt()) {
            throw $this->createNotFoundException();
        }

        $animeReviews = $reviews->findBy(['anime' => $anime], ['createdAt' => 'DESC']);

        $average = null;
        if (count($animeReviews) > 0) {
            $total = 0;
            foreach ($animeReviews as $review) {
                $total += $review->getRating();
            }
            $average = $total / count($animeReviews);
        }

        // Le suivi du membre connecté (null s'il n'en a pas ou s'il n'est pas connecté)
        $tracker = null;
        if ($this->getUser()) {
            $tracker = $trackers->findOneBy(['anime' => $anime, 'user' => $this->getUser()]);
        }

        return $this->render('anime/show.html.twig', [
            'anime' => $anime,
            'reviews' => $animeReviews,
            'average' => $average,
            'canEdit' => $this->canEdit($anime),
            'tracker' => $tracker,
            'statuses' => Tracker::STATUSES,
        ]);
    }

    // Un membre connecté ajoute une fiche
    #[Route('/animes/new', name: 'app_anime_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $anime = new Anime();
        $anime->setIsFeatured(false);
        $form = $this->createForm(AnimeType::class, $anime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $anime->setCreatedBy($this->getUser());
            $em->persist($anime);
            $em->flush();

            return $this->redirectToRoute('app_anime_show', ['id' => $anime->getId()]);
        }

        return $this->render('anime/form.html.twig', ['form' => $form, 'anime' => null]);
    }

    // Modifier : seulement l'auteur de la fiche (ou un admin)
    #[Route('/animes/{id}/edit', name: 'app_anime_edit', requirements: ['id' => '\d+'])]
    public function edit(Anime $anime, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->canEdit($anime)) {
            throw $this->createAccessDeniedException("Tu n'as pas créé cette fiche.");
        }

        $form = $this->createForm(AnimeType::class, $anime);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_anime_show', ['id' => $anime->getId()]);
        }

        return $this->render('anime/form.html.twig', ['form' => $form, 'anime' => $anime]);
    }

    // Supprimer : seulement l'auteur de la fiche (ou un admin)
    #[Route('/animes/{id}/delete', name: 'app_anime_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Anime $anime, EntityManagerInterface $em): Response
    {
        if (!$this->canEdit($anime)) {
            throw $this->createAccessDeniedException("Tu n'as pas créé cette fiche.");
        }

        $anime->setDeletedAt(new \DateTimeImmutable()); // la fiche est masquée, pas effacée
        $em->flush();

        return $this->redirectToRoute('app_anime_index');
    }

    // Donner (ou changer) sa note depuis la fiche
    #[Route('/animes/{id}/review', name: 'app_anime_review', methods: ['POST'])]
    public function review(Anime $anime, Request $request, ReviewRepository $reviews, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        // Un seul avis par membre et par anime : on reprend l'ancien s'il existe
        $review = $reviews->findOneBy(['anime' => $anime, 'user' => $user]);
        if (!$review) {
            $review = new Review();
            $review->setUser($user);
            $review->setAnime($anime);
        }

        $review->setRating($request->request->getInt('rating'));
        $review->setComment($request->request->get('comment'));
        $review->setCreatedAt(new \DateTimeImmutable());

        $em->persist($review);
        $em->flush();

        return $this->redirectToRoute('app_anime_show', ['id' => $anime->getId()]);
    }

    #[Route('/animes/{id}/track', name: 'app_anime_track', methods: ['POST'])]
    public function track(Anime $anime, Request $request, TrackerRepository $trackers, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $user = $this->getUser();

        // Un seul suivi par membre et par anime : on reprend l'ancien s'il existe
        $tracker = $trackers->findOneBy(['anime' => $anime, 'user' => $user]);
        if (!$tracker) {
            $tracker = new Tracker();
            $tracker->setUser($user);
            $tracker->setAnime($anime);
        }

        // Statut inconnu : on met "À voir"
        $status = $request->request->getString('status');
        if (!isset(Tracker::STATUSES[$status])) {
            $status = 'plan_to_watch';
        }

        // Épisodes vus : 0 si "À voir", le nombre saisi si "En cours", tous si "Terminé"
        $total = $anime->getEpisodesCount();
        $episodes = 0;
        if ($status === 'watching') {
            $episodes = max(0, $request->request->getInt('episodes'));
            if ($total) {
                $episodes = min($episodes, $total); // jamais plus que le total
            }
        } elseif ($status === 'completed') {
            $episodes = $total ?? 0;
        }

        $tracker->setStatus($status);
        $tracker->setEpisodesWatched($episodes);
        $tracker->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($tracker);
        $em->flush();

        return $this->redirectToRoute('app_anime_show', ['id' => $anime->getId()]);
    }

    // true si l'utilisateur connecté est l'auteur de la fiche ou un admin
    private function canEdit(Anime $anime): bool
    {
        $user = $this->getUser();

        return $user && ($anime->getCreatedBy() === $user || $this->isGranted('ROLE_ADMIN'));
    }
}

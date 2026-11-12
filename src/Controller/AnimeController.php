<?php

namespace App\Controller;

use App\Entity\Anime;
use App\Entity\Review;
use App\Entity\Tracker;
use App\Entity\User;
use App\Repository\AnimeRepository;
use App\Repository\GenreRepository;
use App\Repository\ReviewRepository;
use App\Repository\TrackerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnimeController extends AbstractController
{
    public const STATUSES = [
        'watching' => 'En cours',
        'completed' => 'Terminé',
        'plan_to_watch' => 'À voir',
        'dropped' => 'Abandonné',
    ];

    #[Route('/animes', name: 'app_anime_index')]
    public function index(Request $request, AnimeRepository $animes, GenreRepository $genres): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $genreId = $request->query->getInt('genre');

        $qb = $animes->createQueryBuilder('a')
            ->leftJoin('a.studio', 's')->addSelect('s')
            ->where('a.deleted_at IS NULL')
            ->orderBy('a.title', 'ASC');
        if ($q !== '') {
            $qb->andWhere('a.title LIKE :q')->setParameter('q', '%'.$q.'%');
        }
        if ($genreId) {
            $qb->andWhere(':genre MEMBER OF a.genres')->setParameter('genre', $genreId);
        }

        return $this->render('anime/index.html.twig', [
            'animes' => $qb->getQuery()->getResult(),
            'genres' => $genres->findBy([], ['name' => 'ASC']),
            'q' => $q,
            'currentGenre' => $genreId,
        ]);
    }

    #[Route('/animes/{id}', name: 'app_anime_show', requirements: ['id' => '\d+'])]
    public function show(Anime $anime, ReviewRepository $reviews, TrackerRepository $trackers): Response
    {
        if ($anime->getDeletedAt()) {
            throw $this->createNotFoundException();
        }
        $animeReviews = $reviews->findBy(['anime' => $anime, 'deletedAt' => null], ['createdAt' => 'DESC']);
        $avg = count($animeReviews)
            ? array_sum(array_map(fn (Review $r) => $r->getRating(), $animeReviews)) / count($animeReviews)
            : null;

        $user = $this->getUser();

        return $this->render('anime/show.html.twig', [
            'anime' => $anime,
            'reviews' => $animeReviews,
            'average' => $avg,
            'tracker' => $user ? $trackers->findOneBy(['anime' => $anime, 'user' => $user]) : null,
            'myReview' => $user ? $reviews->findOneBy(['anime' => $anime, 'user' => $user, 'deletedAt' => null]) : null,
            'statuses' => self::STATUSES,
        ]);
    }

    #[Route('/animes/{id}/review', name: 'app_anime_review', methods: ['POST'])]
    public function review(Anime $anime, Request $request, ReviewRepository $reviews, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if (!$this->isCsrfTokenValid('review'.$anime->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }
        /** @var User $user */
        $user = $this->getUser();
        $rating = max(1, min(10, $request->request->getInt('rating')));

        $review = $reviews->findOneBy(['anime' => $anime, 'user' => $user, 'deletedAt' => null])
            ?? (new Review())->setUser($user)->setAnime($anime);
        $review->setRating($rating)
            ->setComment(trim((string) $request->request->get('comment')) ?: null)
            ->setCreatedAt(new \DateTimeImmutable());
        $em->persist($review);
        $em->flush();
        $this->addFlash('success', 'Avis enregistré.');

        return $this->redirectToRoute('app_anime_show', ['id' => $anime->getId()]);
    }

    #[Route('/reviews/{id}/delete', name: 'app_review_delete', methods: ['POST'])]
    public function deleteReview(Review $review, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if ($review->getUser() !== $this->getUser()
            || !$this->isCsrfTokenValid('del-review'.$review->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
        $review->setDeletedAt(new \DateTimeImmutable()); // soft delete
        $em->flush();
        $this->addFlash('success', 'Avis supprimé.');

        return $this->redirectToRoute('app_anime_show', ['id' => $review->getAnime()->getId()]);
    }

    #[Route('/animes/{id}/track', name: 'app_anime_track', methods: ['POST'])]
    public function track(Anime $anime, Request $request, TrackerRepository $trackers, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        if (!$this->isCsrfTokenValid('track'.$anime->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalide');
        }
        /** @var User $user */
        $user = $this->getUser();
        $tracker = $trackers->findOneBy(['anime' => $anime, 'user' => $user])
            ?? (new Tracker())->setUser($user)->setAnime($anime);

        if ($request->request->get('action') === 'remove') {
            if ($tracker->getId()) {
                $em->remove($tracker);
                $em->flush();
            }
            $this->addFlash('success', 'Retiré de ta liste.');
        } else {
            $status = (string) $request->request->get('status');
            $episodes = max(0, $request->request->getInt('episodes'));
            if ($anime->getEpisodesCount()) {
                $episodes = min($episodes, $anime->getEpisodesCount());
            }
            $tracker->setStatus(array_key_exists($status, self::STATUSES) ? $status : 'plan_to_watch')
                ->setEpisodesWatched($episodes)
                ->setUpdatedAt(new \DateTimeImmutable());
            $em->persist($tracker);
            $em->flush();
            $this->addFlash('success', 'Liste mise à jour.');
        }

        return $this->redirectToRoute('app_anime_show', ['id' => $anime->getId()]);
    }

    // /profile est protégé par access_control (ROLE_USER)
    #[Route('/profile', name: 'app_profile')]
    public function profile(TrackerRepository $trackers, ReviewRepository $reviews): Response
    {
        $user = $this->getUser();

        return $this->render('anime/profile.html.twig', [
            'trackers' => $trackers->findBy(['user' => $user], ['updatedAt' => 'DESC']),
            'reviews' => $reviews->findBy(['user' => $user, 'deletedAt' => null], ['createdAt' => 'DESC']),
            'statuses' => self::STATUSES,
        ]);
    }
}

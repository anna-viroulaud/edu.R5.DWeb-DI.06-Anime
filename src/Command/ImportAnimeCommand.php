<?php

namespace App\Command;

use App\Entity\Anime;
use App\Entity\Genre;
use App\Entity\Studio;
use App\Repository\AnimeRepository;
use App\Repository\GenreRepository;
use App\Repository\StudioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Importe des animes depuis l'API gratuite AniList (GraphQL, sans clé).
 *
 *   bin/console app:import-anime                 → les mieux notés, 1 page (25 animes)
 *   bin/console app:import-anime --pages=4       → 100 animes
 *   bin/console app:import-anime --search=naruto → recherche par titre
 */
#[AsCommand(name: 'app:import-anime', description: 'Importe des animes depuis l\'API AniList')]
class ImportAnimeCommand extends Command
{
    private const API = 'https://graphql.anilist.co';

    private const QUERY = <<<'GRAPHQL'
        query ($page: Int, $perPage: Int, $search: String) {
          Page(page: $page, perPage: $perPage) {
            pageInfo { hasNextPage }
            media(type: ANIME, isAdult: false, search: $search, sort: [SCORE_DESC]) {
              idMal
              title { romaji english }
              description(asHtml: false)
              seasonYear
              episodes
              coverImage { extraLarge large }
              studios(isMain: true) { nodes { name } }
              genres
            }
          }
        }
        GRAPHQL;

    private array $studioCache = [];
    private array $genreCache = [];

    public function __construct(
        private HttpClientInterface $http,
        private EntityManagerInterface $em,
        private AnimeRepository $animes,
        private StudioRepository $studios,
        private GenreRepository $genres,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('pages', 'p', InputOption::VALUE_REQUIRED, 'Nombre de pages de 25 animes', 1)
            ->addOption('search', 's', InputOption::VALUE_REQUIRED, 'Rechercher un titre au lieu du top')
            ->addOption('featured', 'f', InputOption::VALUE_REQUIRED, 'Les N premiers sont mis "à la une"', 5);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $pages = max(1, (int) $input->getOption('pages'));
        $search = $input->getOption('search');
        $featured = (int) $input->getOption('featured');

        $created = $updated = 0;
        $position = 0;

        for ($page = 1; $page <= $pages; ++$page) {
            $io->text("Page {$page}…");

            $response = $this->http->request('POST', self::API, [
                'json' => [
                    'query' => self::QUERY,
                    'variables' => ['page' => $page, 'perPage' => 25, 'search' => $search],
                ],
            ]);

            if ($response->getStatusCode() === 429) { 
                $io->warning('Limite atteinte, pause de 60 s…');
                sleep(60);
                --$page;
                continue;
            }

            $data = $response->toArray()['data']['Page'];

            foreach ($data['media'] as $item) {
                ++$position;
                $title = $item['title']['english'] ?? $item['title']['romaji'];

                // On retrouve l'anime par son id MyAnimeList, sinon par son titre
                $anime = ($item['idMal'] ? $this->animes->findOneBy(['mal_id' => $item['idMal']]) : null)
                    ?? $this->animes->findOneBy(['title' => $title]);
                $anime ? ++$updated : ++$created;
                $anime ??= new Anime();

                $anime
                    ->setMalId($item['idMal'])
                    ->setTitle($title)
                    ->setSynopsis($item['description'] ? trim(strip_tags($item['description'])) : null)
                    ->setReleaseYear($item['seasonYear'])
                    ->setEpisodesCount($item['episodes'])
                    ->setPosterImage($item['coverImage']['extraLarge'] ?? $item['coverImage']['large'] ?? null)
                    ->setIsFeatured(!$search && $position <= $featured)
                    ->setUpdatedAt(new \DateTimeImmutable());

                if ($studioName = $item['studios']['nodes'][0]['name'] ?? null) {
                    $anime->setStudio($this->getStudio($studioName));
                }

                foreach ($item['genres'] as $genreName) {
                    $anime->addGenre($this->getGenre($genreName));
                }

                $this->em->persist($anime);
            }

            $this->em->flush();

            if (!$data['pageInfo']['hasNextPage']) {
                break;
            }
            sleep(1);
        }

        $io->success("Import terminé : $created créé(s), $updated mis à jour.");

        return Command::SUCCESS;
    }

    private function getStudio(string $name): Studio
    {
        return $this->studioCache[$name] ??= $this->studios->findOneBy(['name' => $name])
            ?? $this->persist((new Studio())->setName($name));
    }

    private function getGenre(string $name): Genre
    {
        return $this->genreCache[$name] ??= $this->genres->findOneBy(['name' => $name])
            ?? $this->persist((new Genre())->setName($name));
    }

    private function persist(object $entity): object
    {
        $this->em->persist($entity);

        return $entity;
    }
}

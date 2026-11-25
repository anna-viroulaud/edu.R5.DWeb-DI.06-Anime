<?php

namespace App\Repository;

use App\Entity\Anime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Anime>
 */
class AnimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Anime::class);
    }

    // Les animes du catalogue ; un filtre à 0 est ignoré
    public function findByFilters(int $genre, int $studio, int $year): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.deleted_at IS NULL')
            ->orderBy('a.title', 'ASC');

        if ($genre) {
            $qb->andWhere(':genre MEMBER OF a.genres')->setParameter('genre', $genre);
        }
        if ($studio) {
            $qb->andWhere('a.studio = :studio')->setParameter('studio', $studio);
        }
        if ($year) {
            $qb->andWhere('a.release_year = :year')->setParameter('year', $year);
        }

        return $qb->getQuery()->getResult();
    }

    // Les années présentes dans le catalogue, pour la liste déroulante
    public function findYears(): array
    {
        return $this->createQueryBuilder('a')
            ->select('DISTINCT a.release_year')
            ->where('a.deleted_at IS NULL')
            ->andWhere('a.release_year IS NOT NULL')
            ->orderBy('a.release_year', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();
    }

//    /**
//     * @return Anime[] Returns an array of Anime objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Anime
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}

<?php

namespace App\Repository;

use App\Entity\Scrape;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Scrape|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scrape|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scrape[]    findAll()
 * @method Scrape[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScrapeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scrape::class);
    }

    // /**
    //  * @return Scrape[] Returns an array of Scrape objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Scrape
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function findOldAndWasRead()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.has_been_read = :has_been_read')
            ->setParameter('has_been_read', true)
            ->andWhere('p.posted_at < :posted_at')
            ->setParameter('posted_at', date('Y-m-d H:i:s', strtotime('-3 days')))
            ->orderBy('p.posted_at', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findMoreThan2WeekOldProjects()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.posted_at < :posted_at')
            ->setParameter('posted_at', date('Y-m-d H:i:s', strtotime('-14 days')))
            ->orderBy('p.posted_at', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function getBiddableQuery() {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.should_bid', true));
        $qb->andWhere($qb->expr()->neq('p.has_been_read', true));
        $qb->orderBy('p.posted_at', 'DESC');
        return $qb->getQuery();
    }

    public function findUnread()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.should_bid', true));
        $qb->andWhere($qb->expr()->neq('p.has_been_read', true));
        $qb->orderBy('p.posted_at', 'DESC');
        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Project[] Returns an array of Project objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Project
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}

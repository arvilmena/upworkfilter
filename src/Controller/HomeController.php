<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param ProjectRepository $projectRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(ProjectRepository $projectRepository, PaginatorInterface $paginator, Request $request)
    {
        $qb = $projectRepository->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.should_bid', true));
        $qb->andWhere($qb->expr()->neq('p.has_been_read', true));
        $qb->orderBy('p.posted_at', 'ASC');
        $query = $qb->getQuery();

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            20 /*limit per page*/
        );

        $qb = $projectRepository->createQueryBuilder('p');
        $qb->andWhere($qb->expr()->eq('p.should_bid', true));
        $qb->andWhere($qb->expr()->isNotNull('p.has_been_read_at'));
        $qb->orderBy('p.has_been_read_at', 'DESC');
        $recentlyReadProjects = $qb->setMaxResults(10)->getQuery()->getResult();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'pagination' => $pagination,
            'recentlyReadProjects' => $recentlyReadProjects
        ]);
    }
}

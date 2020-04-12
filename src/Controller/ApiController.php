<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/fetch-later-project-count/{lastId}", name="api-later-project-count")
     */
    public function index(int $lastId, ProjectRepository $projectRepository)
    {
        $qb = $projectRepository->createQueryBuilder('p');
        $qb->andWhere('p.id > :last_id')->setParameter('last_id', $lastId);
        $qb->andWhere($qb->expr()->eq('p.should_bid', true));
        $qb->select('count(p.id)');
        $newProjects = $qb->getQuery()->getSingleScalarResult();

        return new JsonResponse(['newProjects' => (int) $newProjects]);

    }
}

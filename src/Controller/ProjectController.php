<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    /**
     * @Route("/project/read/{id}", name="project-read")
     */
    public function read(int $id, ProjectRepository $projectRepository, EntityManagerInterface $entityManager)
    {
        $project = $projectRepository->find($id);
        if ( !($project instanceof Project) || empty($project->getUrl()) ) {
            return $this->redirectToRoute('home');
        }
        $project->setHasBeenRead(true);
        $project->setHasBeenReadAt(new \DateTime('now'));
        $entityManager->persist($project);
        $entityManager->flush();
        return new RedirectResponse($project->getUrl());
    }
}

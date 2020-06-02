<?php

namespace App\Command;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanListingCommand extends Command
{
    protected static $defaultName = 'app:cleanListing';
    /**
     * @var ProjectRepository
     */
    private $projectRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(string $name = null, ProjectRepository $projectRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->projectRepository = $projectRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        /**
         * @var Project[] $unreadProjects
         */
        $unreadProjects = $this->projectRepository->findUnread();
        if ( empty($unreadProjects) ) {
            $io->success('Nothing to clean.');
            return 0;
        }
        foreach($unreadProjects as $unreadProject) {
            $io->writeln(sprintf('Scraping %s', $unreadProject->getUrl()));
        }

        return 0;
    }
}

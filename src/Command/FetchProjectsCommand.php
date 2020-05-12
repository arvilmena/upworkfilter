<?php

namespace App\Command;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\AppUtils;
use App\Service\Upwork\UpworkProjectListingScraperService;
use App\Service\Upwork\UpworkProjectPageAnalyzerService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FetchProjectsCommand extends Command
{
    protected static $defaultName = 'app:fetchProjects';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var string
     */
    private $crawl_id;
    /**
     * @var AppUtils
     */
    private $appUtils;
    /**
     * @var ProjectRepository
     */
    private $projectRepository;
    /**
     * @var UpworkProjectListingScraperService
     */
    private $upworkProjectListingScraperService;
    /**
     * @var UpworkProjectPageAnalyzerService
     */
    private $upworkProjectPageAnalyzerService;

    public function __construct(string $name = null, EntityManagerInterface $entityManager, AppUtils $appUtils, ProjectRepository $projectRepository, UpworkProjectListingScraperService $upworkProjectListingScraperService, UpworkProjectPageAnalyzerService $upworkProjectPageAnalyzerService)
    {
        parent::__construct($name);
        $this->crawl_id = Uuid::uuid4()->toString();
        $this->entityManager = $entityManager;
        $this->appUtils = $appUtils;
        $this->projectRepository = $projectRepository;
        $this->upworkProjectListingScraperService = $upworkProjectListingScraperService;
        $this->upworkProjectPageAnalyzerService = $upworkProjectPageAnalyzerService;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
        ;
    }

    private function scrapeUpwork(SymfonyStyle $io) {
        $scrape = $this->upworkProjectListingScraperService->scrape($this->crawl_id);

        if ( empty($scrape) ) {
            $io->error('Cannot get the latest project listing for UpWork');
        }

        $this->entityManager->persist($scrape);
        $this->entityManager->flush();

        $xml = new \SimpleXMLElement($scrape->getBody(), LIBXML_NOWARNING | LIBXML_NOERROR);

        $projects = (new \Feed())->toArray($xml);

        if ( empty($projects['channel']['item']) ) {
            $io->error('Cannot navigate to the project list from UpWork RSS');
        }
        $projects = $projects['channel']['item'];
        if ( empty($projects) ) {
            $io->error('No project found on PeoplePerHour RSS');
        }

        $projectRepo = $this->projectRepository;

        $io->writeln(sprintf('> [Upwork] We got %s projects in the list', count($projects)));

        $filteredProjects = array_filter(
            $projects,
            function($project) use ($projectRepo) {
                $existing_record = $projectRepo->findOneBy(['url' => trim($project['link'])]);
                return (!($existing_record instanceof Project));
            }
        );

        $io->writeln(sprintf('> [Upwork] We removed %s projects in the list', count($projects) - count($filteredProjects)));

        $requests = [];
        foreach($filteredProjects as $project) {
            $link = trim($project['link']);
            $io->writeln(sprintf('> scraping %s', $link));
            $description = trim($project['description']);
            preg_match('/<b>Budget<\/b>: (.*)/m', $description, $match);
            $budget = trim($match[1] ?? '');
            preg_match('/<b>Country<\/b>: (.*)/m', $description, $match);
            $country = trim($match[1] ?? '');

            $title = trim($project['title']);
            $title = rtrim($title, '- Upwork');
            $title = trim($title);

            $requests[] = [
                'link' => $link,
                'title' => $title,
                'description' => $description,
                'pubDate' => trim($project['pubDate']),
                'country' => trim($country),
                'budget' => trim($budget),
            ];
        }

        $biddable_projects = 0;
        foreach($requests as $request) {
            $io->writeln(sprintf('> analyzing %s', $request['link']));
            $project = $this->upworkProjectPageAnalyzerService->analyze(
                $request['link'],
                $request['title'],
                $request['description'],
                $request['pubDate'],
                $request['country'],
                $request['budget']
            );
            if ( true === $project->getShouldBid() ) {
                $biddable_projects++;
            }
            $this->entityManager->persist($project);
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        $io->writeln(sprintf('> [Upwork] Total of %d new projects that can be bid on added.', $biddable_projects));

        return $biddable_projects;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->writeln('Deleting old red projects...');
        /**
         * @var Project[] $old_projects
         */
        $old_projects = $this->projectRepository->findOldAndWasRead();

        if ( ! empty($old_projects) ) {
            foreach($old_projects as $old_project) {
                $io->writeln(sprintf("> Deleting \"%s\" posted_at %s", $old_project->getTitle(), $old_project->getPostedAt()->format('F j g:i:a')));
                $this->entityManager->remove($old_project);
            }
        } else {
            $io->writeln('> Nothing to delete');
        }
        $this->entityManager->flush();

        $biddable_projects = 0;

        $io->writeln('Scraping Upwork...');
        $biddable_projects = $biddable_projects + $this->scrapeUpwork($io);

//        $io->writeln('Scraping PeoplePerHour...');
//        $biddable_projects = $biddable_projects + $this->scrapePPH($io);

        if($biddable_projects > 0) {
            $io->success(sprintf('Total of %d new projects that can be bid on added.', $biddable_projects));
        } else {
            $io->error('No new projects added.');
        }


        return 0;
    }
}

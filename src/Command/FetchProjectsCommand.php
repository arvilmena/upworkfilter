<?php

namespace App\Command;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\AppUtils;
use App\Service\PeoplePerHour\PeoplePerHourProjectListingScraperService;
use App\Service\PeoplePerHour\PeoplePerHourProjectPageAnalyzerService;
use App\Service\Upwork\UpworkProjectListingScraperService;
use App\Service\Upwork\UpworkProjectPageAnalyzerService;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FetchProjectsCommand extends Command
{
    protected static $defaultName = 'app:fetchProjects';
    /**
     * @var PeoplePerHourProjectListingScraperService
     */
    private $pphProjectListingScraperService;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var string
     */
    private $crawl_id;
    /**
     * @var PeoplePerHourProjectPageAnalyzerService
     */
    private $pphProjectPageAnalyzerService;
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

    public function __construct(string $name = null, PeoplePerHourProjectListingScraperService $pphProjectListingScraperService, EntityManagerInterface $entityManager, PeoplePerHourProjectPageAnalyzerService $pphProjectPageAnalyzerService, AppUtils $appUtils, ProjectRepository $projectRepository, UpworkProjectListingScraperService $upworkProjectListingScraperService, UpworkProjectPageAnalyzerService $upworkProjectPageAnalyzerService)
    {
        parent::__construct($name);
        $this->crawl_id = Uuid::uuid4()->toString();
        $this->pphProjectListingScraperService = $pphProjectListingScraperService;
        $this->entityManager = $entityManager;
        $this->pphProjectPageAnalyzerService = $pphProjectPageAnalyzerService;
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

    private function scrapePPH(SymfonyStyle $io)
    {
        $scrape = $this->pphProjectListingScraperService->scrape($this->crawl_id);

        if ( empty($scrape) ) {
            $io->error('Cannot get the latest project listing for PeoplePerHour');
            return 0;
        }

        $this->entityManager->persist($scrape);
        $this->entityManager->flush();

        $xml = new \SimpleXMLElement($scrape->getBody(), LIBXML_NOWARNING | LIBXML_NOERROR);

        $pphProjects = (new \Feed())->toArray($xml);

        if ( empty($pphProjects['channel']['item']) ) {
            $io->error('Cannot navigate to the project list from PeoplePerHour RSS');
        }
        $pphProjects = $pphProjects['channel']['item'];
        if ( empty($pphProjects) ) {
            $io->error('No project found on PeoplePerHour RSS');
        }

        $projectRepo = $this->projectRepository;

        $io->writeln(sprintf('> [PPH] We got %s projects in the list', count($pphProjects)));

        $filteredPPHProjects = array_filter(
            $pphProjects,
            function($pphProject) use ($projectRepo) {
                $existing_record = $projectRepo->findOneBy(['url' => trim($pphProject['link'])]);
                return (!($existing_record instanceof Project));
            }
        );

        $io->writeln(sprintf('> [PPH] We removed %s projects in the list', count($pphProjects) - count($filteredPPHProjects)));

        $requests = [];
        foreach($filteredPPHProjects as $pphProject) {
            $link = trim($pphProject['link']);
            $client = HttpClient::createForBaseUri($this->appUtils::getBaseUriFromUrl($link));
            $io->writeln(sprintf('> scraping %s', $link));
            $requests[] = [
                'link' => $link,
                'title' => trim($pphProject['title']),
                'description' => trim($pphProject['description']),
                'pubDate' => trim($pphProject['pubDate']),
                'response' => $client->request('GET', $link)
            ];
        }

        $biddable_projects = 0;
        foreach($requests as $request) {
            $io->writeln(sprintf('> analyzing %s', $request['link']));
            $project = $this->pphProjectPageAnalyzerService->analyze(
                $request['link'],
                $request['title'],
                $request['description'],
                $request['pubDate'],
                $request['response']->getContent()
            );
            if ( true === $project->getShouldBid() ) {
                $biddable_projects++;
            }
            $this->entityManager->persist($project);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        $io->writeln(sprintf('> [PPH] Total of %d new projects that can be bid on added.', $biddable_projects));

        return $biddable_projects;
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

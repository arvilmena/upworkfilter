<?php

namespace App\Command;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\AppUtils;
use App\Service\PeoplePerHour\PeoplePerHourProjectListingScraperService;
use App\Service\PeoplePerHour\PeoplePerHourProjectPageAnalyzerService;
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

    public function __construct(string $name = null, PeoplePerHourProjectListingScraperService $pphProjectListingScraperService, EntityManagerInterface $entityManager, PeoplePerHourProjectPageAnalyzerService $pphProjectPageAnalyzerService, AppUtils $appUtils, ProjectRepository $projectRepository)
    {
        parent::__construct($name);
        $this->crawl_id = Uuid::uuid4()->toString();
        $this->pphProjectListingScraperService = $pphProjectListingScraperService;
        $this->entityManager = $entityManager;
        $this->pphProjectPageAnalyzerService = $pphProjectPageAnalyzerService;
        $this->appUtils = $appUtils;
        $this->projectRepository = $projectRepository;
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

        $scrape = $this->pphProjectListingScraperService->scrape($this->crawl_id);

        if ( empty($scrape) ) {
            $io->error('Cannot get the latest project listing for PeoplePerHour');
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

        $io->note(sprintf('We got %s projects in the list', count($pphProjects)));

        $filteredPPHProjects = array_filter(
            $pphProjects,
            function($pphProject) use ($projectRepo) {
                $existing_record = $projectRepo->findOneBy(['url' => trim($pphProject['link'])]);
                return (!($existing_record instanceof Project));
            }
        );

        $io->note(sprintf('We removed %s projects in the list', count($pphProjects) - count($filteredPPHProjects)));

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

        foreach($requests as $request) {
            $io->writeln(sprintf('> analyzing %s', $request['link']));
            $project = $this->pphProjectPageAnalyzerService->analyze(
                $request['link'],
                $request['title'],
                $request['description'],
                $request['pubDate'],
                $request['response']->getContent()
            );
            $this->entityManager->persist($project);
        }

        $this->entityManager->flush();

        return 0;
    }
}

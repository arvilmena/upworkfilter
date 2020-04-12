<?php
  /**
   * PeoplePerHourProjectPageAnalyzerService.php.php file summary.
   *
   * PeoplePerHourProjectPageAnalyzerService.php.php file description.
   *
   * @link       https://project.com
   *
   * @package    Project
   * @subpackage App\Service\PeoplePerHour
   * @author     Arvil Meña <arvil@arvilmena.com>
   * @since      1.0.0
   */

declare(strict_types=1);
namespace App\Service\PeoplePerHour;

use App\Entity\Project;
use App\Service\AppConstants;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class PeoplePerHourProjectPageAnalyzerService.
 *
 * Class PeoplePerHourProjectPageAnalyzerService description.
 *
 * @since 1.0.0
 */

class PeoplePerHourProjectPageAnalyzerService {

    /**
     * @var AppConstants
     */
    private $appConstants;

    public function __construct(AppConstants $appConstants)
    {
        $this->appConstants = $appConstants;
    }

    public function analyze(string $url, string $title, string $description, string $pubDate, string $pageHtml) {
        $crawler = new Crawler($pageHtml);

        $project = new Project($url, $title);
        $project->setPostedAt($this->getPostedAt($pubDate));
        $project->setDescription($description);
        $project->setLocation($this->getLocation($crawler));
        $project->setClientReviewRating($this->getClientReviewRating($crawler));
        $project->setRawHtml($pageHtml);
        $project->setClientName($this->getClientName($crawler));

        if ( true === $this->hasBadLocation($project->getLocation())) {
            $project->setShouldBid(false);
        }
        if ( 0 === $project->getClientReviewRating() || empty($project->getClientReviewRating()) ) {
            $project->setShouldBid(false);
        }

        $price = $this->getBudget($crawler);
        if ( ! empty($price) ) {
            $project->setBudget($price);
        }
        if ( ! empty($project->getBudget()) && 1 === preg_match('/\/hr/m', $project->getBudget()) ) {
            $project->setShouldBid(false);
        }

        if ( false !== $project->getShouldBid() ) {
            $project->setShouldBid(true);
        }

        return $project;
    }

    private function getBudget(Crawler $crawler) : ?string {
        try {
            return $crawler->filter('#main-container .budget .price-approx')->text();
        } catch (\Throwable $e) {
        }
        try {
            return $crawler->filter('#main-container .budget .price-tag')->text();
        } catch (\Throwable $e) {
        }
        return null;
    }

    private function hasBadLocation($location) : bool {
        if (empty($location) || ! is_string($location)) {
            return false;
        }
        $location = ucwords($location);
        foreach($this->appConstants::BAD_COUNTRY_ARRAY as $bad_country) {
            if (1 === preg_match("/" . $bad_country . "/", $location)) {
                return true;
            }
        }
        return false;
    }

    private function getClientName(Crawler $crawler) : ?string {
        try {
            return $crawler->filter('#main-container div.summary.member-summary-section div.member-information-container .member-name-container span.member-short-name')->text();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getLocation(Crawler $crawler) : ?string {
        try {
            return $crawler->filter('#main-container div.location.member-summary-section .location-container')->text();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function getClientReviewRating(Crawler $crawler) : ?int {
        try {
            $text = $crawler->filter('#main-container .summary.member-summary-section div.member-information-container span[title="User Rating and Reviews"] span.value')->text();
        } catch (\Throwable $e) {
            return null;
        }
        if ( empty($text) && 0 !== (int) $text ) {
            return null;
        }
        return (int) filter_var($text, FILTER_SANITIZE_NUMBER_INT);
    }

    private function getPostedAt(string $pubDate) : ?\DateTime {
        try {
            return new \DateTime($pubDate);
        } catch (\Exception $e) {
            return null;
        }
    }

}
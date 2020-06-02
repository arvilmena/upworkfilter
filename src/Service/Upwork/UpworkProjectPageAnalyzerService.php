<?php
  /**
   * UpworkProjectPageAnalyzerService.php.php file summary.
   *
   * UpworkProjectPageAnalyzerService.php.php file description.
   *
   * @link       https://project.com
   *
   * @package    Project
   * @subpackage App\Service\Upwork
   * @author     Arvil Meña <arvil@arvilmena.com>
   * @since      1.0.0
   */

declare(strict_types=1);
namespace App\Service\Upwork;

use App\Entity\Project;
use App\Service\AppConstants;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class UpworkProjectPageAnalyzerService.
 *
 * Class UpworkProjectPageAnalyzerService description.
 *
 * @since 1.0.0
 */
class UpworkProjectPageAnalyzerService {

    /**
     * @var AppConstants
     */
    private $appConstants;

    public function __construct(AppConstants $appConstants)
    {
        $this->appConstants = $appConstants;
    }

    public function analyze(string $url, string $title, string $description, string $pubDate, string $country, $budget, string $pageHtml = null)
    {

        $project = new Project($url, $title);
        $project->setPostedAt($this->getPostedAt($pubDate));
        $project->setDescription($description);
        $project->setLocation($country);

        if ( ! empty($pageHtml) ) {
            $project->setRawHtml($pageHtml);
        }

        if ( true === $this->hasBadLocation($project->getLocation())) {
            $project->setShouldBid(false);
        }

        if ( ! empty($budget) ) {
            $project->setBudget($budget);
            $budget_float_form = (float) filter_var( $budget, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
            if ( 0 !== (int) $budget_float_form && $budget < 15 ) {
                $project->setShouldBid(false);
            }

        }

        if ( false !== $project->getShouldBid() ) {
            $project->setShouldBid(true);
        }

        return $project;
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

    private function getClientReviewRating(Crawler $crawler) : ?int {
        try {
            $text = $crawler->filter('#layout visitor-job-details div.jd-card > div > aside.sidebar-extra.col-lg-3 > div > section > div > div > div > span.nowrap > span > span:nth-child(1)')->text();
        } catch (\Throwable $e) {
            return null;
        }
        if ( empty($text) && 0 !== (int) $text ) {
            return null;
        }
        return (int) (5 / $text * 100);
    }

    private function getPostedAt(string $pubDate) : ?\DateTime {
        try {
            return new \DateTime($pubDate);
        } catch (\Exception $e) {
            return null;
        }
    }

}
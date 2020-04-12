<?php
  /**
   * UpworkProjectListingScraperService.php.php file summary.
   *
   * UpworkProjectListingScraperService.php.php file description.
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

use App\Entity\Scrape;
use App\Service\AppConstants;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class UpworkProjectListingScraperService.
 *
 * Class UpworkProjectListingScraperService description.
 *
 * @since 1.0.0
 */
class UpworkProjectListingScraperService {

    const FEED_URL = 'https://www.upwork.com/ab/feed/jobs/rss?client_hires=1-9%2C10-&verified_payment_only=1&q=wordpress&sort=recency&subcategory2_uid=531770282584862733&job_type=fixed&paging=0%3B50&api_params=1';
    /**
     * @var CurlHttpClient
     */
    private $httpClient;
    /**
     * @var AppConstants
     */
    private $appConstants;

    public function __construct(AppConstants $appConstants)
    {
        $this->httpClient = new CurlHttpClient();
        $this->appConstants = $appConstants;
    }

    public function scrape(string $crawl_id = null) : ?Scrape {

        try {
            $response = $this->httpClient->request('GET', static::FEED_URL);
        } catch (TransportExceptionInterface $e) {
            return null;
        }

        if ( empty($crawl_id) ) {
            $crawl_id = Uuid::uuid4()->toString();
        }

        $scrape = new Scrape($crawl_id, static::FEED_URL);
        $scrape->setType($this->appConstants::PROJECT_LISTING_TYPE);

        // Body.
        try {
            $body = $response->getContent();
        } catch (\Throwable $e) {
            $body = null;
        }
        if ( empty($body) ) {
            return null;
        }
        $scrape->setBody($body);

        // Status code.
        try {
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            $statusCode = null;
        }
        if ( ! empty($statusCode) ) {
            $scrape->setStatusCode($statusCode);
        }

        return $scrape;
    }
}
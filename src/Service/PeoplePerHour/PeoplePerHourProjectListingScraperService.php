<?php
  /**
   * PeoplePerHourProjectListingScraperService.php.php file summary.
   *
   * PeoplePerHourProjectListingScraperService.php.php file description.
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

use App\Entity\Scrape;
use App\Service\AppConstants;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class PeoplePerHourProjectListingScraperService.
 *
 * Class PeoplePerHourProjectListingScraperService description.
 *
 * @since 1.0.0
 */
class PeoplePerHourProjectListingScraperService {
    
    const FEED_URL = 'https://www.peopleperhour.com/feed/jobs?term=wordpress&sort=latest&locationFilter=remote&pricing=fixed_price';

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
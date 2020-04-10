<?php
  /**
   * AppUtils.php.php file summary.
   *
   * AppUtils.php.php file description.
   *
   * @link       https://project.com
   *
   * @package    Project
   * @subpackage App\Service
   * @author     Arvil Meña <arvil@arvilmena.com>
   * @since      1.0.0
   */

declare(strict_types=1);
namespace App\Service;

/**
 * Class AppUtils.
 *
 * Class AppUtils description.
 *
 * @since 1.0.0
 */
class AppUtils {

    public static function getBaseUriFromUrl($url) : ?string {
        if ( empty($url)) {
            return null;
        }
        $data = parse_url(trim($url));
        if (empty($data['scheme']) || empty($data['host'])) {
            return null;
        }
        return $data['scheme'] . '://' . $data['host'];
    }
    

}
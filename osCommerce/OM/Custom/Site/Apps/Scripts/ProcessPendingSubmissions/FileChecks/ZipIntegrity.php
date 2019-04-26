<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions\FileChecks;

class ZipIntegrity implements \osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions\FileChecksInterface
{
    public static $priority = 200;
    public static $public_fail_error = 'General error';

    public static function execute(string $file): bool
    {
        $ext = pathinfo($file, \PATHINFO_EXTENSION);

        if (strtolower($ext) !== 'zip') {
            return true;
        }

        $zip = new \ZipArchive();
        $res = $zip->open($file, \ZipArchive::CHECKCONS);
        $zip->close();

        if ($res !== true) {
            switch ($res) {
                case \ZipArchive::ER_NOZIP:
                    static::$public_fail_error = 'Not ZIP file';
                    break;

                case \ZipArchive::ER_INCONS :
                    static::$public_fail_error = 'Consistency check failed';
                    break;

                case \ZipArchive::ER_CRC :
                    static::$public_fail_error = 'Checksum failed';
                    break;
            }

            return false;
        }

        return true;
    }
}

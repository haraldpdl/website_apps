<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions\FileChecks;

class ZipIntegrity extends \osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions\FileChecksAbstract
{
    public static $priority = 200;

    public $public_fail_error = 'General error';

    public function execute(): bool
    {
        $ext = pathinfo($this->file, \PATHINFO_EXTENSION);

        if (strtolower($ext) !== 'zip') {
            return true;
        }

        $zip = new \ZipArchive();
        $res = $zip->open($this->file, \ZipArchive::CHECKCONS);
        $zip->close();

        if ($res !== true) {
            switch ($res) {
                case \ZipArchive::ER_NOZIP:
                    $this->public_fail_error = 'Not ZIP file';
                    break;

                case \ZipArchive::ER_INCONS :
                    $this->public_fail_error = 'Consistency check failed';
                    break;

                case \ZipArchive::ER_CRC :
                    $this->public_fail_error = 'Checksum failed';
                    break;
            }

            return false;
        }

        return true;
    }
}

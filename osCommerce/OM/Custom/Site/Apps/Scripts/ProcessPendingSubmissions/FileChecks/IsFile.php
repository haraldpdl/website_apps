<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions\FileChecks;

class IsFile extends \osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions\FileChecksAbstract
{
    public static $priority = 10;

    public $public_fail_error = 'File not found';

    public function execute(): bool
    {
        return is_file($this->file);
    }
}

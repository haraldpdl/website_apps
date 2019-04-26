<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions\FileChecks;

class IsFile implements \osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions\FileChecksInterface
{
    public static $priority = 10;
    public static $public_fail_error = 'File not found';

    public static function execute(string $file): bool
    {
        return is_file($file);
    }
}

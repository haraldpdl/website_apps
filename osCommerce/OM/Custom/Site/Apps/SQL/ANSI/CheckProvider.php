<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class CheckProvider
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        $Qcheck = $OSCOM_PDO->get('website_apps_providers', 'id', [
            'code' => $params['provider']
        ], null, 1);

        return $Qcheck->fetch() !== false;
    }
}

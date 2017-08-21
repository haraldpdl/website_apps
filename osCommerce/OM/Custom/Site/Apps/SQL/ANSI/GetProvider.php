<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetProvider
{
    public static function execute(array $params): array
    {
        $OSCOM_PDO = Registry::get('PDO');

        $Qprovider = $OSCOM_PDO->get('website_apps_providers', [
            'code',
            'title'
        ], [
            'code' => $params['provider']
        ]);

        return $Qprovider->fetch();
    }
}

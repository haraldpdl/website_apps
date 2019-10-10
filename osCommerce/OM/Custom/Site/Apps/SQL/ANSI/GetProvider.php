<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
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

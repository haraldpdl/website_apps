<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetVersions
{
    public static function execute()
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $Qversions = $OSCOM_PDO_OLD->prepare('select id, code, title from contrib_versions where status = 1 order by sort_order, title');
        $Qversions->setCache('apps-versions');
        $Qversions->execute();

        return $Qversions->fetchAll();
    }
}

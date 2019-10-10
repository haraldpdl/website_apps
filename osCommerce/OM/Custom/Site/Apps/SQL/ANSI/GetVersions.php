<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetVersions
{
    public static function execute()
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $Qversions = $OSCOM_PDO_OLD->query('select v.id, v.code, v.title, g.title as group_title from contrib_versions v, contrib_versions_groups g where v.status = 1 and v.group_id = g.id and g.status = 1 order by g.sort_order, v.sort_order, v.title');

        return $Qversions->fetchAll();
    }
}

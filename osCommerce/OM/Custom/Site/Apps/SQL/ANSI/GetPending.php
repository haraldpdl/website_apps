<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetPending
{
    public static function execute(array $params)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $sql_query = 'select * from :table_website_apps_pending where process_status is null order by date_added';

        if (isset($params['limit']) && is_int($params['limit'])) {
            $sql_query .= ' limit ' . (int)$params['limit'];
        }

        $Qpending = $OSCOM_PDO->query($sql_query);

        return $Qpending->fetchAll();
    }
}

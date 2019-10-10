<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetCategories
{
    public static function execute(array $params)
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        if (isset($params['version'])) {
            $query = 'select c.id, c.code, c.title from contrib_categories c, contrib_packages p, contrib_versions v where c.status = 1 and c.id = p.contrib_categories_id and p.status = 1 and p.contrib_versions_id = v.id and v.code = :version_code and v.status = 1 group by c.id order by c.sort_order, c.title';
        } else {
            $query = 'select id, code, title from contrib_categories where status = 1 order by sort_order, title';
        }

        $Qcategories = $OSCOM_PDO_OLD->prepare($query);

        if (isset($params['version'])) {
            $Qcategories->bindValue(':version_code', $params['version']);
        }

        $Qcategories->execute();

        return $Qcategories->fetchAll();
    }
}

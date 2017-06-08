<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class CheckPublicId
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        if (isset($params['strict']) && ($params['strict'] === true)) {
            $sql = 'select p.id from contrib_packages p, contrib_categories c, contrib_versions v, contrib_files f where p.public_id = :public_id and p.status = 1 and p.contrib_categories_id = c.id and c.status = 1 and p.contrib_versions_id = v.id and v.status = 1 and p.id = f.contrib_packages_id and f.status = 1 limit 1';
        } else {
            $sql = 'select id from contrib_packages where public_id = :public_id limit 1';
        }

        $Qcheck = $OSCOM_PDO_OLD->prepare($sql);
        $Qcheck->bindValue(':public_id', $params['public_id']);
        $Qcheck->execute();

        return $Qcheck->fetch() !== false;
    }
}

<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class CheckFilePublicId
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $sql = 'select f.id from contrib_packages p, contrib_files f where p.public_id = :addon_public_id and p.id = f.contrib_packages_id and f.public_id = :public_id';

        if (isset($params['strict']) && ($params['strict'] === true)) {
            $sql .= ' and p.status = :status and f.status = :status';
        }

        $sql .= ' limit 1';

        $Qcheck = $OSCOM_PDO_OLD->prepare($sql);
        $Qcheck->bindValue(':addon_public_id', $params['addon_public_id']);
        $Qcheck->bindValue(':public_id', $params['public_id']);

        if (isset($params['strict']) && ($params['strict'] === true)) {
            $Qcheck->bindInt(':status', 1);
        }

        $Qcheck->execute();

        return $Qcheck->fetch() !== false;
    }
}

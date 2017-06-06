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

        $sql = 'select id from contrib_packages where public_id = :public_id';

        if (isset($params['strict']) && ($params['strict'] === true)) {
            $sql .= ' and status = :status';
        }

        $sql .= ' limit 1';

        $Qcheck = $OSCOM_PDO_OLD->prepare($sql);
        $Qcheck->bindValue(':public_id', $params['public_id']);

        if (isset($params['strict']) && ($params['strict'] === true)) {
            $Qcheck->bindInt(':status', 1);
        }

        $Qcheck->execute();

        return $Qcheck->fetch() !== false;
    }
}

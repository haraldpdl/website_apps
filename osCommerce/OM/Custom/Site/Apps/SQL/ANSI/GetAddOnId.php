<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetAddOnId
{
    public static function execute(array $params): array
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $Qinfo = $OSCOM_PDO_OLD->prepare('select id from contrib_packages where public_id = :public_id');
        $Qinfo->bindValue(':public_id', $params['public_id']);
        $Qinfo->execute();

        return $Qinfo->fetch();
    }
}

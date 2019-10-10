<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetAddOnPublicId
{
    public static function execute(array $params): array
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $Qinfo = $OSCOM_PDO_OLD->prepare('select public_id from contrib_packages where id = :id');
        $Qinfo->bindInt(':id', $params['id']);
        $Qinfo->execute();

        return $Qinfo->fetch();
    }
}

<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

use osCommerce\OM\Core\Site\Apps\Apps;

class GetAddOnAuthors
{
    public static function execute(array $params): array
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $Qauthors = $OSCOM_PDO_OLD->prepare('select a.userprofile_id as id from contrib_admins a, contrib_packages p where p.public_id = :public_id and p.id = a.contrib_packages_id');
        $Qauthors->bindValue(':public_id', $params['public_id']);
        $Qauthors->execute();

        return $Qauthors->fetchAll();
    }
}

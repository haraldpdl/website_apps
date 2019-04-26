<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class IsAddOnInQueue
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        $Qcheck = $OSCOM_PDO->prepare('select id from :table_website_apps_pending where (public_id = :public_id or parent_public_id = :parent_public_id) and process_status is null limit 1');
        $Qcheck->bindValue(':public_id', $params['public_id']);
        $Qcheck->bindValue(':parent_public_id', $params['public_id']);
        $Qcheck->execute();

        return $Qcheck->fetch() !== false;
    }
}

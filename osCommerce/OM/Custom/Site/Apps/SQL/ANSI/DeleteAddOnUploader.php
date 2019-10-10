<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class DeleteAddOnUploader
{
    public static function execute(array $params): bool
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        return $OSCOM_PDO_OLD->delete('contrib_admins', [
            'contrib_packages_id' => $params['id'],
            'userprofile_id' => $params['user_id']
        ], [
            'prefix_tables' => false
        ]) === 1;
    }
}

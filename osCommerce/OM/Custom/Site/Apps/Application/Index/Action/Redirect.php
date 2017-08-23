<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Index\Action;

use osCommerce\OM\Core\{
    ApplicationAbstract,
    OSCOM,
    Registry
};

class Redirect
{
    public static function execute(ApplicationAbstract $application)
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        if (is_numeric($_GET['Redirect']) && ((int)$_GET['Redirect'] < 9574)) { // 9573 last public add-on ID
            $Qapp = $OSCOM_PDO_OLD->get('contrib_packages', 'public_id', [
                'id' => (int)$_GET['Redirect']
            ], null, null, [
                'prefix_tables' => false
            ]);

            if ($Qapp->fetch() !== false) {
                OSCOM::redirect('https://apps.oscommerce.com/' . $Qapp->value('public_id'), 301);
            }
        }

        OSCOM::redirect('https://apps.oscommerce.com', 301);
    }
}

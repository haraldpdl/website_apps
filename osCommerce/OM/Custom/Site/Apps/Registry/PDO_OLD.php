<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Registry;

use osCommerce\OM\Core\{
    OSCOM,
    PDO
};

class PDO_OLD extends \osCommerce\OM\Core\RegistryAbstract
{
    public function __construct()
    {
        $this->value = PDO::initialize(OSCOM::getConfig('legacy_db_server', 'Apps'), OSCOM::getConfig('legacy_db_server_username', 'Apps'), OSCOM::getConfig('legacy_db_server_password', 'Apps'), OSCOM::getConfig('legacy_db_database', 'Apps'), null, OSCOM::getConfig('db_driver', 'Website'));
    }
}

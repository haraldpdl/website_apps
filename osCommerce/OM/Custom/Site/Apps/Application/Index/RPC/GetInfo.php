<?php
/**
 * osCommerce Website
 *
 * @copyright Copyright (c) 2014 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Apps\Application\Index\RPC;

  use osCommerce\OM\Core\OSCOM;
  use osCommerce\OM\Core\Registry;

  class GetInfo {
    public static function execute() {
      $OSCOM_PDO = Registry::get('PDO');

      $result = [ 'rpcStatus' => '-100' ];

      echo json_encode($result);
    }
  }
?>

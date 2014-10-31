<?php
/**
 * osCommerce Website
 *
 * @copyright Copyright (c) 2014 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

  use osCommerce\OM\Core\Registry;

  class LogGetUpdates {
    public static function execute($data) {
      $OSCOM_PDO = Registry::get('PDO');

      $Qfile = $OSCOM_PDO->prepare('insert into :table_website_apps_files_update_check_log (file_id, date_added, ip_address) values (:file_id, now(), :ip_address)');
      $Qfile->bindInt(':file_id', $data['id']);
      $Qfile->bindValue(':ip_address', $data['ip_address']);
      $Qfile->execute();

      return $Qfile->rowCount();
    }
  }
?>

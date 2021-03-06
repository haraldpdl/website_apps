<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class LogDownload
{
    public static function execute($data)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $Qfile = $OSCOM_PDO->prepare('insert into :table_website_apps_files_download_log (file_id, file_type, date_added, ip_address) values (:file_id, :file_type, now(), :ip_address)');
        $Qfile->bindInt(':file_id', $data['id']);
        $Qfile->bindInt(':file_type', $data['type'] == 'full' ? 1 : 2);
        $Qfile->bindValue(':ip_address', $data['ip_address']);
        $Qfile->execute();

        return $Qfile->rowCount();
    }
}

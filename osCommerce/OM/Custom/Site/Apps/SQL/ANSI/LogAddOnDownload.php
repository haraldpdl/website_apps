<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class LogAddOnDownload
{
    public static function execute(array $data): int
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $Qfile = $OSCOM_PDO_OLD->prepare('select f.contrib_packages_id, f.id from contrib_packages p, contrib_files f where p.public_id = :app_code and p.id = f.contrib_packages_id and f.public_id = :file_code');
        $Qfile->bindValue(':app_code', $data['app_code']);
        $Qfile->bindValue(':file_code', $data['file_code']);
        $Qfile->execute();

        if ($Qfile->fetch()) {
            $Qcounter = $OSCOM_PDO_OLD->prepare('update contrib_files set downloads = (downloads + 1) where id = :id');
            $Qcounter->bindInt(':id', $Qfile->valueInt('id'));
            $Qcounter->execute();

            $Qlog = $OSCOM_PDO_OLD->prepare('insert into contrib_download_log (contrib_packages_id, contrib_files_id, date_added, ipaddress, user_id) values (:contrib_packages_id, :contrib_files_id, now(), :ipaddress, :user_id)');
            $Qlog->bindInt(':contrib_packages_id', $Qfile->valueInt('contrib_packages_id'));
            $Qlog->bindInt(':contrib_files_id', $Qfile->valueInt('id'));
            $Qlog->bindValue(':ipaddress', $data['ip_address']);
            $Qlog->bindInt(':user_id', $data['user_id']);
            $Qlog->execute();

            return $Qlog->rowCount();
        }

        return 0;
    }
}

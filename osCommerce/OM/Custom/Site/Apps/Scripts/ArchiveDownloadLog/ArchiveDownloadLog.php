<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Scripts\ArchiveDownloadLog;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

class ArchiveDownloadLog implements \osCommerce\OM\Core\RunScriptInterface
{
    public static function execute()
    {
        OSCOM::initialize('Apps');

        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $Qlog = $OSCOM_PDO_OLD->query('select * from contrib_download_log where date_added < date_sub(curdate(), interval 1 day)');

        while ($Qlog->fetch()) {
            if ($OSCOM_PDO_OLD->save('contrib_download_log_archive', [
                'id' => $Qlog->valueInt('id'),
                'contrib_packages_id' => $Qlog->valueInt('contrib_packages_id'),
                'contrib_files_id' => $Qlog->valueInt('contrib_files_id'),
                'date_added' => $Qlog->value('date_added'),
                'ipaddress' => $Qlog->value('ipaddress'),
                'user_id' => $Qlog->value('user_id')
            ], null, [
                'prefix_tables' => false
            ]) === 1) {
                $OSCOM_PDO_OLD->delete('contrib_download_log', [
                    'id' => $Qlog->valueInt('id')
                ], [
                    'prefix_tables' => false
                ]);
            }
        }
    }
}

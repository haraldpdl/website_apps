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

class GetAddOnFiles
{
    public static function execute(array $params): array
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $Qfiles = $OSCOM_PDO_OLD->prepare('select left(f.title, :title_length) as title, f.description, f.public_id, f.author_name, f.userprofile_id, date_format(f.date_added, "%Y%m%d") as date_added from contrib_files f, contrib_packages p where p.public_id = :public_id and p.id = f.contrib_packages_id and f.status = 1 order by f.date_added desc');
        $Qfiles->bindInt(':title_length', Apps::TITLE_LENGTH);
        $Qfiles->bindValue(':public_id', $params['public_id']);
        $Qfiles->execute();

        return $Qfiles->fetchAll();
    }
}

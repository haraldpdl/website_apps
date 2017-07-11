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

class GetUserApps
{
    public static function execute(array $params)
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $Qlisting = $OSCOM_PDO_OLD->prepare('select left(p.title, :title_length) as title, left(p.short_description, :short_description_length) as short_description, p.public_id, p.cover_image, if(p.public_flag = 1, 1, null) as open_flag, c.code as category_code, v.code as version_code, date_format(max(f.date_added), "%Y%m%d %H%i%s") as last_update_date, if(ma.id > 0, 1, null) as certified from contrib_files f, contrib_packages p left join modules_addons ma on (p.id = ma.addons_package_id), contrib_categories c, contrib_versions v where p.userprofile_id = :userprofile_id and p.status = 1 and p.id = f.contrib_packages_id and f.status = 1 and p.contrib_categories_id = c.id and c.status = 1 and p.contrib_versions_id = v.id and v.status = 1 group by f.contrib_packages_id order by title');
        $Qlisting->bindInt(':title_length', Apps::TITLE_LENGTH);
        $Qlisting->bindInt(':short_description_length', Apps::SHORT_DESCRIPTION_LENGTH);
        $Qlisting->bindInt(':userprofile_id', $params['user_id']);
        $Qlisting->execute();

        return $Qlisting->fetchAll();
    }
}

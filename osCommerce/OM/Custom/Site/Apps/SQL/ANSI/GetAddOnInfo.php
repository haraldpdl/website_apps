<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

use osCommerce\OM\Core\Site\Apps\Apps;

class GetAddOnInfo
{
    public static function execute(array $params): array
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $Qinfo = $OSCOM_PDO_OLD->prepare('select left(p.title, :title_length) as title, left(p.short_description, :short_description_length) as short_description, p.description, p.public_id, p.userprofile_id, p.cover_image, p.screenshot_images, if(p.public_flag = 1, 1, null) as open_flag, p.support_topic, p.support_forum, p.prev_contrib_versions_id as prev_versions_id, p.prev_contrib_categories_id as prev_categories_id, c.title as category_title, c.code as category_code, v.title as version_title, v.code as version_code, count(f.id) as total_files, if(ma.id > 0, 1, null) as certified from contrib_packages p left join modules_addons ma on (p.id = ma.addons_package_id), contrib_categories c, contrib_versions v, contrib_files f where p.public_id = :public_id and p.status = 1 and p.contrib_categories_id = c.id and c.status = 1 and p.contrib_versions_id = v.id and v.status = 1 and p.id = f.contrib_packages_id and f.status = 1 group by p.id');
        $Qinfo->bindInt(':title_length', Apps::TITLE_LENGTH);
        $Qinfo->bindInt(':short_description_length', Apps::SHORT_DESCRIPTION_LENGTH);
        $Qinfo->bindValue(':public_id', $params['public_id']);
        $Qinfo->execute();

        return $Qinfo->fetch();
    }
}

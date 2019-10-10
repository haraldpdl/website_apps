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

class GetProviderApps
{
    public static function execute(array $params): array
    {
        $OSCOM_PDO = Registry::get('PDO');
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $result = [];

        $Qprovider = $OSCOM_PDO->get([
            'website_apps_providers p',
            'website_apps_legacy l'
        ], [
            'l.contrib_packages_id'
        ], [
            'p.code' => $params['provider'],
            'p.id' => [
                'rel' => 'l.provider_id'
            ]
        ]);

        $ids = [];

        while ($Qprovider->fetch()) {
            $ids[] = $Qprovider->valueInt('contrib_packages_id');
        }

        if (!empty($ids)) {
            $Qlisting = $OSCOM_PDO_OLD->prepare('select left(p.title, :title_length) as title, left(p.short_description, :short_description_length) as short_description, p.public_id, p.cover_image, if(p.public_flag = 1, 1, null) as open_flag, c.code as category_code, v.code as version_code, date_format(max(f.date_added), "%Y%m%d %H%i%s") as last_update_date, if(ma.id > 0, 1, null) as certified from contrib_files f, contrib_packages p left join modules_addons ma on (p.id = ma.addons_package_id), contrib_categories c, contrib_versions v where p.id in (' . implode(', ', $ids) . ') and p.status = 1 and p.id = f.contrib_packages_id and f.status = 1 and p.contrib_categories_id = c.id and c.status = 1 and p.contrib_versions_id = v.id and v.status = 1 group by f.contrib_packages_id order by title');
            $Qlisting->bindInt(':title_length', Apps::TITLE_LENGTH);
            $Qlisting->bindInt(':short_description_length', Apps::SHORT_DESCRIPTION_LENGTH);
            $Qlisting->execute();

            $result = $Qlisting->fetchAll();
        }

        return $result;
    }
}

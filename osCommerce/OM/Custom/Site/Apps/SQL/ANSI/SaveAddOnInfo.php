<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class SaveAddOnInfo
{
    public static function execute(array $data): bool
    {
        $OSCOM_PDO_OLD = Registry::get('PDO_OLD');

        $row = [
            'title' => $data['title'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'support_topic' => $data['support_topic'],
            'cover_image' => $data['cover_image'],
            'screenshot_images' => $data['screenshot_images'],
            'public_flag' => $data['public_flag']
        ];

        if (isset($data['version_id']) && isset($data['prev_version_id'])) {
            $row['contrib_versions_id'] = $data['version_id'];
            $row['prev_contrib_versions_id'] = $data['prev_version_id'];
        }

        if (isset($data['category_id']) && isset($data['prev_category_id'])) {
            $row['contrib_categories_id'] = $data['category_id'];
            $row['prev_contrib_categories_id'] = $data['prev_category_id'];
        }

        return $OSCOM_PDO_OLD->save('contrib_packages', $row, [
            'public_id' => $data['public_id']
        ], [
            'prefix_tables' => false
        ]) === 1;
    }
}

<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
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
            'cover_image' => $data['cover_image'],
            'screenshot_images' => $data['screenshot_images'],
            'public_flag' => $data['public_flag']
        ];

        return $OSCOM_PDO_OLD->save('contrib_packages', $row, [
            'public_id' => $data['public_id']
        ], [
            'prefix_tables' => false
        ]) === 1;
    }
}

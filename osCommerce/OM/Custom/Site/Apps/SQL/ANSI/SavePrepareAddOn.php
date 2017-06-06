<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class SavePrepareAddOn
{
    public static function execute(array $data): bool
    {
        $OSCOM_PDO = Registry::get('PDO');

        $row = [
            'title' => $data['title'],
            'description' => $data['description'],
            'user_id' => (int)$data['user_id'],
            'date_added' => 'now()',
            'ip_address' => $data['ip_address']
        ];

        if (isset($data['short_description'])) {
            $row['short_description'] = $data['short_description'];
        }

        if (isset($data['versions_id'])) {
            $row['versions_id'] = (int)$data['versions_id'];
        }

        if (isset($data['categories_id'])) {
            $row['categories_id'] = (int)$data['categories_id'];
        }

        if (isset($data['zip_file'])) {
            $row['zip_file'] = $data['zip_file'];
        }

        if (isset($data['cover_image'])) {
            $row['cover_image'] = $data['cover_image'];
        }

        if (isset($data['screenshot_images'])) {
            $row['screenshot_images'] = $data['screenshot_images'];
        }

        if (isset($data['uploaders'])) {
            $row['uploaders'] = $data['uploaders'];
        }

        if (isset($data['public_id'])) {
            $row['public_id'] = $data['public_id'];
        }

        if (isset($data['parent_public_id'])) {
            $row['parent_public_id'] = $data['parent_public_id'];
        }

        return $OSCOM_PDO->save('website_apps_pending', $row) === 1;
    }
}

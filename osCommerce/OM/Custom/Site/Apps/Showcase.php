<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps;

use osCommerce\OM\Core\OSCOM;

class Showcase
{
    public static function get()
    {
        $data = [
            'ver_major' => $matches[1],
            'ver_minor' => $matches[2],
            'ver_patch' => $matches[3]
        ];

        return OSCOM::callDB('Apps\GetShowcase', $data, 'Site');
    }
}

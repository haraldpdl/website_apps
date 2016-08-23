<?php
/**
 * osCommerce Website
 *
 * @copyright (c) 2016 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/bsdlicense.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Index\RPC;

use osCommerce\OM\Core\{
    HTML,
    OSCOM
};

use osCommerce\OM\Core\Site\Website\Releases;

use osCommerce\OM\Core\Site\RPC\Controller as RPC;

class GetShowcase
{
    public static function execute()
    {
        $result = [
            'rpcStatus' => '-100'
        ];

        $keys = array_keys($_GET);
        $req = array_slice($keys, array_search(substr(get_called_class(), strrpos(get_called_class(), '\\')+1), $keys) + 1);

        if (count($req) >= 1) {
            $oscom_version = HTML::sanitize($req[0]);

            if (preg_match('/^(\d+)\_(\d+)\_(\d+)$/', $oscom_version, $matches) === 1) {
                $data = [
                    'ver_major' => $matches[1],
                    'ver_minor' => $matches[2],
                    'ver_patch' => $matches[3]
                ];

                $version = $data['ver_major'] . '.' . $data['ver_minor'] . '.' . $data['ver_patch'];

                if (Releases::versionExists($version) && Releases::hasApps($version)) {
                    $showcase = OSCOM::callDB('Apps\GetShowcase', $data, 'Site');

                    if (count($showcase) > 0) {
                        $result['rpcStatus'] = RPC::STATUS_SUCCESS;

                        $result['showcase'] = $showcase;
                    } else {
                        $result['rpcStatus'] = '-300';
                    }
                } else {
                    $result['rpcStatus'] = '-200';
                }
            }
        }

        echo json_encode($result);
    }
}

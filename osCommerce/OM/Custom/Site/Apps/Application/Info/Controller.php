<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Info;

use osCommerce\OM\Core\{
    HTML,
    OSCOM
};

use osCommerce\OM\Core\Site\Apps\Apps;

class Controller extends \osCommerce\OM\Core\Site\Apps\ApplicationAbstract
{
    protected function initialize()
    {
        header('X-Robots-Tag: none');

        $keys = array_keys($_GET);
        $req = array_slice($keys, array_search(OSCOM::getSiteApplication(), $keys) + 1);

        if (count($req) >= 2) {
            $provider = HTML::sanitize(basename($req[0]));
            $app = HTML::sanitize(basename($req[1]));

            $data = [
                'provider' => $provider,
                'app' => $app,
                'language_id' => 1
            ];

            $info = OSCOM::callDB('Apps\GetInfo', $data, 'Site');

            if ((count($info) > 0) && ($info['legacy_addon_id'] > 0)) {
                $app = Apps::getAddOnInfo(Apps::getPublicId($info['legacy_addon_id']));

                OSCOM::redirect(OSCOM::getLink('Apps', 'Index', $app['public_id'] . '&' . $app['title_slug']));
            }
        }

        exit;
    }
}

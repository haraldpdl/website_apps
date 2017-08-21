<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\SQL\ANSI;

use osCommerce\OM\Core\Registry;

class GetShowcase
{
    public static function execute($data)
    {
        $OSCOM_PDO = Registry::get('PDO');

        $result = [];

        $Qshowcase = $OSCOM_PDO->prepare('select p.code as provider, a.code as app, d.title, d.description from :table_website_apps_showcase s, :table_website_apps a, :table_website_apps_providers p, :table_website_apps_description d where s.app_id = a.id and a.provider_id = p.id and a.id = d.app_id and d.language_id = :language_id order by s.sort_order');
        $Qshowcase->bindInt(':language_id', 1);
        $Qshowcase->setCache('apps-showcase-lang1-' . $data['ver_major'] . '_' . $data['ver_minor']);
        $Qshowcase->execute();

        if ($Qshowcase->fetch() !== false) {
            do {
// need to update the backend to use the new Vendor App format
                $vendor = $Qshowcase->value('provider');
                $app = $Qshowcase->value('app');

                if ($vendor == 'paypal') {
                    $vendor = 'PayPal';
                }

                if (($vendor == 'PayPal') && ($app == 'app')) {
                    $app = 'PayPal';
                }

                $result[] = [
                    'vendor' => $vendor,
                    'app' => $app,
                    'title' => $Qshowcase->value('title'),
                    'description' => $Qshowcase->value('description')
                ];
            } while ($Qshowcase->fetch());
        }

        return $result;
    }
}

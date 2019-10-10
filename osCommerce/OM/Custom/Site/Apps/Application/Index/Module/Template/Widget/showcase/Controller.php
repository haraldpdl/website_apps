<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Index\Module\Template\Widget\showcase;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

class Controller extends \osCommerce\OM\Core\Template\WidgetAbstract
{
    public static function execute($param = null)
    {
        $OSCOM_Language = Registry::get('Language');
        $OSCOM_PDO = Registry::get('PDO');
        $OSCOM_Template = Registry::get('Template');

        $qf_pageset = $OSCOM_Template->getValue('qf_pageset');
        $current_version = $OSCOM_Template->getValue('current_version');
        $current_category = $OSCOM_Template->getValue('current_category');

        $content = '';

        if (($qf_pageset['current'] < 2) && empty($current_version) && empty($current_category)) {
            $languages = [
                $OSCOM_Language->getCode()
            ];

            if ($OSCOM_Language->getID() != $OSCOM_Language->getDefaultId()) {
                $languages[] = $OSCOM_Language->getCodeFromID($OSCOM_Language->getDefaultId());
            }

            $data = [
                'type' => 'apps-frontpage',
                'default_language_id' => $OSCOM_Language->getDefaultId()
            ];

            if ($OSCOM_Language->getID() != $OSCOM_Language->getDefaultId()) {
                $data['language_id'] = $OSCOM_Language->getID();
            }

            $carousels = [];

            foreach ($OSCOM_PDO->call('Site\\Website\\GetFrontPageCarousel', $data) as $c) {
                if ($c['partner_id'] > 0) {
                    foreach ($languages as $l) {
                        if (file_exists(OSCOM::getConfig('dir_fs_public', 'OSCOM') . 'sites/' . OSCOM::getSite() . '/images/carousel-frontpage/' . $l . '/' . $c['image'])) {
                            $carousels[] = [
                                'url' =>  $OSCOM_Template->parseContent($c['url']),
                                'image' => $OSCOM_Template->parseContent('{publiclink}images/carousel-frontpage/' . $l . '/' . $c['image'] . '{publiclink}'),
                                'title' => $c['title']
                            ];

                            break;
                        }
                    }
                }
            }

            $OSCOM_Template->setValue('carousel-frontpage', $carousels);

            $file = OSCOM::BASE_DIRECTORY . 'Custom/Site/' . OSCOM::getSite() . '/Application/' . OSCOM::getSiteApplication() . '/Module/Template/Widget/showcase/pages/main.html';

            $content = file_get_contents($file);
        }

        return $content;
    }
}

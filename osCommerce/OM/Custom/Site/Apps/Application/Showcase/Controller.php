<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Showcase;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

class Controller extends \osCommerce\OM\Core\Site\Apps\ApplicationAbstract
{
    protected function initialize()
    {
        $OSCOM_Template = Registry::get('Template');

        $data = [
            'ver_major' => '2',
            'ver_minor' => '3',
            'ver_patch' => '0'
        ];

        $showcase = [];

        foreach (OSCOM::callDB('Apps\GetShowcase', $data, 'Site') as $s) {
            $showcase[] = [
                'code' => $s['vendor'],
                'title' => $s['title'],
                'description' => $s['description'],
                'image' => strtolower($s['vendor'] . '_' . $s['app'] . '.png')
            ];
        }

        $OSCOM_Template->setValue('showcase', $showcase);

        $js = <<<EOD
OSCOM.ready(() => {
    document.querySelector('#nbShowcase').classList.add('active');
});
EOD;

        $OSCOM_Template->addJavascriptBlock($js);

        $this->_page_contents = 'main.html';
        $this->_page_title = OSCOM::getDef('html_page_title');
    }
}

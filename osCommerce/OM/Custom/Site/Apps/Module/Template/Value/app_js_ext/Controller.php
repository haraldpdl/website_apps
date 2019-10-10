<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Module\Template\Value\app_js_ext;

use osCommerce\OM\Core\Registry;

class Controller extends \osCommerce\OM\Core\Template\ValueAbstract
{
    public static function execute(): string
    {
        $OSCOM_Template = Registry::get('Template');

        $result = '';

        if ($OSCOM_Template->hasExternalJavascript()) {
            $result = $OSCOM_Template->getExternalJavascript();
        }

        return $result;
    }
}

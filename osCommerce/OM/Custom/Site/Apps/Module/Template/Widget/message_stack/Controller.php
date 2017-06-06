<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Module\Template\Widget\message_stack;

use osCommerce\OM\Core\Registry;

class Controller extends \osCommerce\OM\Core\Template\WidgetAbstract
{
    public static function execute($group = null)
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');

        if ($OSCOM_MessageStack->exists($group)) {
            return $OSCOM_MessageStack->get($group);
        }
    }
}

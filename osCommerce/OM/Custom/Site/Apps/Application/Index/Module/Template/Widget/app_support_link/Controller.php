<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Index\Module\Template\Widget\app_support_link;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

class Controller extends \osCommerce\OM\Core\Template\WidgetAbstract
{
    public static function execute($param = null)
    {
        $OSCOM_Template = Registry::get('Template');

        $content = '';

        $addon = $OSCOM_Template->getValue('addon');

        if (isset($addon['support_forum'])) {
            $content = '<a href="https://forums.oscommerce.com/forum/' . $addon['support_forum']['id'] . '-' . $addon['support_forum']['title_seo'] . '" class="btn btn-outline-primary">' . OSCOM::getDef('button_support_forum') . '</a>';
        } elseif (isset($addon['support_topic'])) {
            $content = '<a href="https://forums.oscommerce.com/topic/' . $addon['support_topic']['id'] . '-' . $addon['support_topic']['title_seo'] . '" class="btn btn-outline-primary">' . OSCOM::getDef('button_support_topic') . '</a>';
        }

        return $content;
    }
}

<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Index\Module\Template\Value\main_app_card_file;

use osCommerce\OM\Core\Registry;

class Controller extends \osCommerce\OM\Core\Template\ValueAbstract
{
    public static function execute(): string
    {
        $OSCOM_Template = Registry::get('Template');

        return $OSCOM_Template->getPageContentsFile('main_app_card.mustache');
    }
}

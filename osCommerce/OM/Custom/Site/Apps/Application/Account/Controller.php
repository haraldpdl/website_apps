<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Account;

use osCommerce\OM\Core\{
    OSCOM,
    Registry
};

class Controller extends \osCommerce\OM\Core\Site\Apps\ApplicationAbstract
{
    protected function initialize()
    {
        $OSCOM_Template = Registry::get('Template');

        $OSCOM_Template->addHtmlElement('header', '<meta name="robots" content="noindex, nofollow">');

        if (isset($_SESSION['Website']['Account'])) {
            if (empty($this->getRequestedActions())) {
                OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication()));
            }
        } else {
            if (empty($this->getRequestedActions())) {
                $this->runAction('Login');
            }
        }
    }
}

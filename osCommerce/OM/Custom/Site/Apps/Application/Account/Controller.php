<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
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
        $OSCOM_Session = Registry::get('Session');
        $OSCOM_Template = Registry::get('Template');

        if (!$OSCOM_Session->hasStarted()) {
            $OSCOM_Session->start();
        }

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

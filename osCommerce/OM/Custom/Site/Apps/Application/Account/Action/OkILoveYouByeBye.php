<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Account\Action;

use osCommerce\OM\Core\{
    ApplicationAbstract,
    OSCOM,
    Registry
};

class OkILoveYouByeBye
{
    public static function execute(ApplicationAbstract $application)
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_Template = Registry::get('Template');

        $application->setPageParameters();

        $params = $OSCOM_Template->getValue('url_params');

        if (!isset($_SESSION['Website']['Account'])) {
            OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)));
        }

        $_SESSION['logout_redirect'] = [
            'url' => OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params))
        ];

        $OSCOM_MessageStack->add(OSCOM::getDefaultSiteApplication(), OSCOM::getDef('ms_logout_success'), 'success');

        OSCOM::redirect(OSCOM::getLink('Website', 'Account', 'OkILoveYouByeBye', 'SSL'));
    }
}

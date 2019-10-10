<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Account\Action;

use osCommerce\OM\Core\{
    ApplicationAbstract,
    OSCOM,
    Registry
};

class Create
{
    public static function execute(ApplicationAbstract $application)
    {
        $OSCOM_Template = Registry::get('Template');

        $application->setPageParameters();

        $params = $OSCOM_Template->getValue('url_params');

        if (isset($_SESSION['Website']['Account'])) {
            OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)));
        }

        $_SESSION['login_redirect'] = [
            'url' => OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)),
            'cancel_url' => OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)),
            'cancel_text' => OSCOM::getDef('redirect_cancel_return_to_site')
        ];

        OSCOM::redirect(OSCOM::getLink('Website', 'Account', 'Create', 'SSL'));
    }
}

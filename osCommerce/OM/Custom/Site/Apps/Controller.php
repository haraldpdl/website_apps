<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps;

use osCommerce\OM\Core\{
//    Events,
    Hash,
    HTML,
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Website\Invision;

class Controller implements \osCommerce\OM\Core\SiteInterface
{
    protected static $_default_application = 'Index';

    public static function initialize()
    {
        Registry::addAliases([
            'Cache' => 'Core\Site\Website\Registry\Cache',
            'Language' => 'Core\Site\Website\Registry\Language',
            'PDO' => 'Core\Site\Website\Registry\PDO',
            'Session' => 'Core\Site\Website\Registry\Session',
            'Template' => 'Core\Site\Website\Registry\Template'
        ]);

        $OSCOM_Session = Registry::get('Session');
        $OSCOM_Session->setLifeTime(3600);

        if (!OSCOM::isRPC()) {
            if (isset($_COOKIE[$OSCOM_Session->getName()])) {
                $OSCOM_Session->start();

                if (!isset($_SESSION['Website']['Account']) && (OSCOM::getSiteApplication() != 'Account')) {
                    $OSCOM_Session->kill();
                }
            }

            if (!$OSCOM_Session->hasStarted() || !isset($_SESSION['Website']['Account'])) {
                $user = Invision::canAutoLogin();

                if (is_array($user) && isset($user['id'])) {
//                    Events::fire('auto_login-before', $user);

                    if (($user['verified'] === true) && ($user['banned'] === false)) {
                        if (!$OSCOM_Session->hasStarted()) {
                            $OSCOM_Session->start();
                        }

                        $_SESSION['Website']['Account'] = $user;

                        if (!isset($_SESSION['Website']['public_token'])) {
                            $_SESSION['Website']['public_token'] = Hash::getRandomString(32);
                        }

                        $OSCOM_Session->recreate();

//                        Events::fire('auto_login-after');
                    } else {
                        Invision::killCookies();
                    }
                }
            }
        }

        $OSCOM_Template = Registry::get('Template');
        $OSCOM_Template->set('Covfefe');

        $OSCOM_Language = Registry::get('Language');

        $OSCOM_Template->addHtmlTag('dir', $OSCOM_Language->getTextDirection());
        $OSCOM_Template->addHtmlTag('lang', OSCOM::getDef('html_lang_code'));

        $OSCOM_Template->addHtmlElement('header', '<meta name="generator" content="osCommerce Apps Marketplace v' . HTML::outputProtected(OSCOM::getVersion(OSCOM::getSite())) . '">');

        $application = __NAMESPACE__ . '\\Application\\' . OSCOM::getSiteApplication() . '\\Controller';
        Registry::set('Application', new $application());

        $OSCOM_Template->setApplication(Registry::get('Application'));

        $OSCOM_Template->setValue('html_tags', $OSCOM_Template->getHtmlTags());
        $OSCOM_Template->setValue('html_head_tags', $OSCOM_Template->getHtmlElements('head'));
        $OSCOM_Template->setValue('html_page_title', $OSCOM_Template->getPageTitle());
        $OSCOM_Template->setValue('html_page_contents_file', $OSCOM_Template->getPageContentsFile());
        $OSCOM_Template->setValue('html_base_href', $OSCOM_Template->getBaseUrl());
        $OSCOM_Template->setValue('html_header_tags', $OSCOM_Template->getHtmlElements('header'));
        $OSCOM_Template->setValue('current_year', date('Y'));
        $OSCOM_Template->setValue('public_token', $_SESSION['Website']['public_token'] ?? null);

        if (isset($_SESSION['Website']['Account'])) {
            $OSCOM_Template->setValue('user', $_SESSION['Website']['Account']);
        }
    }

    public static function getDefaultApplication()
    {
        return static::$_default_application;
    }

    public static function hasAccess($application)
    {
        return true;
    }
}

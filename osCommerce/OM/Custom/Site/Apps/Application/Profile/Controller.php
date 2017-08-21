<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Profile;

use osCommerce\OM\Core\{
    HTML,
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Apps\Apps;

use osCommerce\OM\Core\Site\Website\{
    Invision,
    Users
};

use Cocur\Slugify\Slugify;

class Controller extends \osCommerce\OM\Core\Site\Apps\ApplicationAbstract
{
    protected function initialize()
    {
        $OSCOM_Template = Registry::get('Template');

        $req = $req_user = $req_developer = null;

        if (count($_GET) > 0) {
            $req_actions = $this->getRequestedActions();
            $req_actions_counter = 0;

            foreach (array_keys($_GET) as $g) {
                $g = HTML::sanitize(basename($g));

                if (!isset($req) && (($g === OSCOM::getSite()) || ($g === OSCOM::getSiteApplication()))) {
                    continue;
                } elseif (!empty($req_actions) && isset($req_actions[$req_actions_counter]) && ($g === $req_actions[$req_actions_counter])) {
                    $req_actions_counter += 1;

                    continue;
                }

                if (preg_match('/^([0-9]+)(\-.+)?$/', $g, $matches) === 1) {
                    if (Invision::checkMemberExists($matches[1], 'id')) {
                        $req = $req_user = $matches[1];
                    }
                } elseif (preg_match('/^([A-Za-z0-9]+)$/', $g, $matches) === 1) {
                    if (Apps::providerExists($matches[1])) {
                        $req = $req_developer = $matches[1];
                    }
                }

                break;
            }
        }

        if (!isset($req)) {
            OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication()));
        }

        if (isset($req_user)) {
            $user = Users::get($req_user);

            $valid = false;

            if (is_array($user) && isset($user['id'])) {
                if (($user['verified'] === true) && ($user['banned'] === false)) {
                    $valid = true;
                }
            }

            if ($valid === false) {
                OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication()));
            }

            $user_apps = Apps::getUserApps($user['id']);
            $user_contributions = Apps::getUserContributions($user['id']);

            if (empty($user_apps) && empty($user_contributions)) {
                OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication()));
            }

            $this->setPageParameters();

            $user['formatted_name'] = strip_tags($user['formatted_name']);

            $slugify = new Slugify();
            $user['name_slug'] = $slugify->slugify($user['name']);

            $OSCOM_Template->setValue('user_profile', $user);
            $OSCOM_Template->setValue('user_profile_apps', $user_apps);
            $OSCOM_Template->setValue('user_profile_contributions', $user_contributions);

            $OSCOM_Template->setValue('user_profile_show_highlights', in_array($user['group_id'], [
                Users::GROUP_ADMIN_ID,
                Users::GROUP_TEAM_CORE_ID,
                Users::GROUP_TEAM_COMMUNITY_ID,
                Users::GROUP_PARTNER_ID,
                Users::GROUP_AMBASSADOR_ID
            ]));

            $OSCOM_Template->setValue('main_url', OSCOM::getLink('Apps', 'Profile', $user['id'] . '-' . $user['name_slug'], 'AUTO'));
            $OSCOM_Template->setValue('addon_url', OSCOM::getLink('Apps', 'Index', 'ADDON_CODE&ADDON_SLUG'));

            $OSCOM_Template->addHtmlElement('header', '<link rel="canonical" href="' . OSCOM::getLink('Apps', 'Profile', $user['id'] . '-' . $user['name_slug'], 'SSL', false) . '">');

            $this->_page_contents = 'main.html';
            $this->_page_title = OSCOM::getDef('html_page_title', [
                ':user' => $user['name']
            ]);
        } else {
            $this->setPageParameters();

            $provider = Apps::getProvider($req_developer);

            $OSCOM_Template->setValue('provider', $provider);
            $OSCOM_Template->setValue('provider_apps', Apps::getProviderApps($provider['code']));

            $OSCOM_Template->setValue('main_url', OSCOM::getLink('Apps', 'Profile', $provider['code'], 'AUTO'));
            $OSCOM_Template->setValue('addon_url', OSCOM::getLink('Apps', 'Index', 'ADDON_CODE&ADDON_SLUG'));

            $OSCOM_Template->addHtmlElement('header', '<link rel="canonical" href="' . OSCOM::getLink('Apps', 'Profile', $provider['code'], 'SSL', false) . '">');

            $this->_page_contents = 'developer.html';
            $this->_page_title = OSCOM::getDef('html_page_title_provider', [
                ':provider' => $provider['title']
            ]);
        }
    }
}

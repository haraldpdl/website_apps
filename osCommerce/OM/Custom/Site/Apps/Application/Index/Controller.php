<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Index;

use osCommerce\OM\Core\{
    HTML,
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Apps\Apps;

class Controller extends \osCommerce\OM\Core\Site\Apps\ApplicationAbstract
{
    protected function initialize()
    {
        $OSCOM_Template = Registry::get('Template');

        if (OSCOM::isRPC()) {
            return true;
        }

        $this->setPageParameters();

        $current_app = $OSCOM_Template->getValue('current_app');

        if (!empty($current_app)) {
            $addon = Apps::getAddOnInfo($current_app);
            $addon_authors = Apps::getAddOnAuthors($current_app, false);

            $OSCOM_Template->setValue('addon', $addon);
            $OSCOM_Template->setValue('addon_files', Apps::getAddOnFiles($current_app));

            $OSCOM_Template->setValue('addon_download_url', OSCOM::getLink('Apps', 'Index', 'Get&' . $current_app . '&FILE_CODE'));

            $is_owner = (($addon['userprofile_id'] > 0) && isset($_SESSION['Website']['Account']) && ($addon['userprofile_id'] == $_SESSION['Website']['Account']['id'])) ? true : false;
            $can_upload_update = (($is_owner === true) || ($addon['open_flag'] == '1') || (isset($_SESSION['Website']['Account']) && in_array($_SESSION['Website']['Account']['id'], $addon_authors))) ? true : false;

            $OSCOM_Template->setValue('is_owner', $is_owner);
            $OSCOM_Template->setValue('can_upload_update', $can_upload_update);

            if (count($this->getRequestedActions()) < 1) {
                $OSCOM_Template->addHtmlElement('header', '<link rel="canonical" href="' . OSCOM::getLink('Apps', 'Index', $current_app . '&' . $addon['title_slug'], 'SSL', false) . '">');
            }

            $this->_page_contents = 'addon.html';
            $this->_page_title = OSCOM::getDef('html_page_title_app', [
                ':title' => $addon['title']
            ]);

            return true;
        }

        $q = $OSCOM_Template->getValue('search_keywords');
        $qs = $OSCOM_Template->getValue('search_sort');
        $current_version = $OSCOM_Template->getValue('current_version');
        $current_category = $OSCOM_Template->getValue('current_category');
        $p = $OSCOM_Template->getValue('current_pageset');

        $OSCOM_Template->setValue('main_url', OSCOM::getLink('Apps', 'Index', (isset($q) ? 'q=' . $q . (isset($qs) ? '&qs=' . $qs : '') : '')));
        $OSCOM_Template->setValue('search_url', OSCOM::getLink('Apps', 'Index', (!empty($current_version) ? 'v=' . $current_version . '&' : '') . 'q=QUERY'));
        $OSCOM_Template->setValue('versions_url', OSCOM::getLink('Apps', 'Index', 'v=VERSION_CODE' . (isset($q) ? '&q=' . $q . (isset($qs) ? '&qs=' . $qs : '') : '')));
        $OSCOM_Template->setValue('categories_url', OSCOM::getLink('Apps', 'Index', (!empty($current_version) ? 'v=' . $current_version . '&' : '') . 'c=CATEGORY_CODE'));

        if (isset($q)) {
            $listing = Apps::getSearchListing($q, $qs, $current_version, $p);
        } else {
            $listing = Apps::getListing($current_category, $current_version, $p);
        }

        $OSCOM_Template->setValue('qf', $listing['entries']);
        $OSCOM_Template->setValue('qf_url', OSCOM::getLink('Apps', 'Index', (!empty($current_version) ? 'v=' . $current_version . '&' : '') . (!empty($current_category) ? 'c=' . $current_category . '&' : '') . (isset($q) ? 'q=' . $q . (isset($qs) ? '&qs=' . $qs : '') . '&' : '') . 'p=PAGESET'));

        $pageset = [
            'current' => $p,
            'has_next' => (($p * 24) < $listing['total'])
        ];

        $OSCOM_Template->setValue('qf_pageset', $pageset);

        $OSCOM_Template->setValue('addon_url', OSCOM::getLink('Apps', 'Index', 'ADDON_CODE&ADDON_SLUG' . (!empty($current_version) ? '&v=' . $current_version : '') . (!empty($current_category) ? '&c=' . $current_category : '') . ($p > 1 ? '&p=' . $p : '')));

        if (count($this->getRequestedActions()) < 1) {
            $OSCOM_Template->addHtmlElement('header', '<link rel="canonical" href="' . OSCOM::getLink('Apps', 'Index', $OSCOM_Template->getValue('url_params_string'), 'SSL', false) . '">');
        }

        $this->_page_contents = 'main.html';
        $this->_page_title = OSCOM::getDef('html_page_title');

        if (!empty($current_category)) {
            $this->_page_title = OSCOM::getDef('html_page_title_category', [
                ':title' => Apps::getCategoryTitle($current_category)
            ]);
        }

        if (isset($q)) {
            if (empty($listing['entries'])) {
                $OSCOM_Template->addHtmlElement('header', '<meta name="robots" content="noindex, nofollow">');
            }

            $OSCOM_Template->setValue('qs_url_relevancy', OSCOM::getLink('Apps', 'Index', (!empty($current_version) ? 'v=' . $current_version . '&' : '') . (!empty($current_category) ? 'c=' . $current_category . '&' : '') . 'q=' . $q));
            $OSCOM_Template->setValue('qs_url_date', OSCOM::getLink('Apps', 'Index', (!empty($current_version) ? 'v=' . $current_version . '&' : '') . (!empty($current_category) ? 'c=' . $current_category . '&' : '') . 'q=' . $q . '&qs=date'));

            $this->_page_contents = 'search.html';
            $this->_page_title = OSCOM::getDef('html_page_title_search', [
                ':keywords' => $q
            ]);
        }
    }
}

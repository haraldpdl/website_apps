<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps;

use osCommerce\OM\Core\{
    HTML,
    OSCOM,
    Registry
};

abstract class ApplicationAbstract extends \osCommerce\OM\Core\ApplicationAbstract
{
    public function setPageParameters()
    {
        $OSCOM_Template = Registry::get('Template');

        $req = null;

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

                if (strlen($g) === 5) {
                    if (Apps::exists($g)) {
                        $req = $g;
                    }
                }

                break;
            }
        }

        $OSCOM_Template->setValue('current_app', $req);

        $versions = [];

        foreach (Apps::getVersions() as $v) {
            $versions[] = [
                'code' => $v['code'],
                'title' => $v['title']
            ];
        }

        $current_version = '';

        if (isset($_GET['v']) && !empty($_GET['v'])) {
          foreach ($versions as $v) {
            if ($_GET['v'] == $v['code']) {
              $current_version = $v['code'];

              break;
            }
          }
        }

        $OSCOM_Template->setValue('versions', $versions);
        $OSCOM_Template->setValue('current_version', $current_version);

        $categories = [];

        foreach (Apps::getCategories($current_version) as $c) {
            $categories[] = [
                'code' => $c['code'],
                'title' => $c['title']
            ];
        }

        $current_category = '';

        if (isset($_GET['c']) && !empty($_GET['c'])) {
          foreach ($categories as $c) {
            if ($_GET['c'] == $c['code']) {
              $current_category = $c['code'];

              break;
            }
          }
        }

        $OSCOM_Template->setValue('categories', $categories);
        $OSCOM_Template->setValue('current_category', $current_category);

        $q = null;

        if (isset($_GET['q']) && !empty($_GET['q'])) {
            $q = Apps::filterSearchKeywords($_GET['q']);

            if (empty($q)) {
                $q = null;
            }
        }

        $OSCOM_Template->setValue('search_keywords', $q);

        $qs = null;

        if (isset($_GET['qs']) && ($_GET['qs'] == 'date')) {
            $qs = 'date';
        }

        $OSCOM_Template->setValue('search_sort', $qs);

        $p = 1;

        if (isset($_GET['p']) && is_numeric($_GET['p']) && ($_GET['p'] > 1)) {
            $p = (int)$_GET['p'];
        }

        $OSCOM_Template->setValue('current_pageset', $p);

        $params = [];

        if (isset($req)) {
            $params[] = $req;
            $params[] = Apps::getAddOnInfo($req, 'title_slug');
        }

        if (!empty($current_version)) {
            $params[] = 'v=' . $current_version;
        }

        if (!empty($current_category)) {
            $params[] = 'c=' . $current_category;
        }

        if (isset($q)) {
            $params[] = 'q=' . $q;

            if (isset($qs)) {
                $params[] = 'qs=' . $qs;
            }
        }

        if ($p > 1) {
            $params[] = 'p=' . $p;
        }

        $OSCOM_Template->setValue('url_params', $params);
        $OSCOM_Template->setValue('url_params_string', implode('&', $params));
    }
}

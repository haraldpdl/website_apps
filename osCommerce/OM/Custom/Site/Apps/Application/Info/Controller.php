<?php
/**
 * osCommerce Website
 *
 * @copyright Copyright (c) 2014 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Apps\Application\Info;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;

  class Controller extends \osCommerce\OM\Core\Site\Apps\ApplicationAbstract {
    protected function initialize() {
      header('X-Robots-Tag: none');

      $keys = array_keys($_GET);
      $req = array_slice($keys, array_search(OSCOM::getSiteApplication(), $keys) + 1);

      if ( count($req) >= 2 ) {
        $provider = HTML::sanitize(basename($req[0]));
        $app = HTML::sanitize(basename($req[1]));

        $data = [ 'provider' => $provider,
                  'app' => $app,
                  'language_id' => 1
                ];

        $info = OSCOM::callDB('Apps\GetInfo', $data, 'Site');

        if ( (count($info) > 0) && ($info['legacy_addon_id'] > 0) ) {
          OSCOM::redirect('http://addons.oscommerce.com/info/' . $info['legacy_addon_id']);
        }
      }

      exit;
    }
  }
?>

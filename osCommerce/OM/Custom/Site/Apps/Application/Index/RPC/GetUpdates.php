<?php
/**
 * osCommerce Website
 *
 * @copyright Copyright (c) 2014 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Apps\Application\Index\RPC;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;

  use osCommerce\OM\Core\Site\RPC\Controller as RPC;

  class GetUpdates {
    public static function execute() {
      $result = [ 'rpcStatus' => '-100' ];

      $keys = array_keys($_GET);
      $req = array_slice($keys, array_search(substr(get_called_class(), strrpos(get_called_class(), '\\')+1), $keys) + 1);

      if ( count($req) >= 4 ) {
        $provider = HTML::sanitize(basename($req[0]));
        $app = HTML::sanitize(basename($req[1]));
        $dep = number_format(str_replace('_', '.', HTML::sanitize(basename($req[2]))), 3);
        $base_version = number_format(str_replace('_', '.', HTML::sanitize(basename($req[3]))), 3);

        $data = [ 'provider' => $provider,
                  'app' => $app,
                  'language_id' => 1
                ];

        $info = OSCOM::callDB('Apps\GetInfo', $data, 'Site');

        if ( (count($info) > 0) && isset($info['releases'][$dep]) ) {
          $result['rpcStatus'] = RPC::STATUS_SUCCESS;

          foreach ( $info['releases'][$dep] as $file ) {
            if ( $file['version'] > $base_version ) {
              $result['app']['releases'][] = $file;
            }
          }
        }
      }

// for store installations without json_decode()
      if ( isset($_GET['format']) && ($_GET['format'] == 'simple') ) {
        $simple = [ 'rpcStatus' => $result['rpcStatus'] ];

        if ( isset($result['app']['releases']) ) {
          $v = [ ];

          foreach ( $result['app']['releases'] as $rel ) {
            $v[] = $rel['version'];
          }

          $simple['version'] = max($v);
        }

        echo http_build_query($simple);
      } else {
        echo json_encode($result);
      }
    }
  }
?>

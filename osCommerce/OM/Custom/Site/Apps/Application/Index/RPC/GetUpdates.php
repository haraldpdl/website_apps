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
        $dep = str_replace('_', '.', HTML::sanitize(basename($req[2])));
        $base_version = str_replace('_', '.', HTML::sanitize(basename($req[3])));

        $legacy = false;

        if (preg_match('/([0-9])\.([0-9])([0-9]{2})?/', $dep, $matches)) {
          $legacy = true;

          $minor = 0;

          if (isset($matches[3])) {
            $minor = ltrim($matches[3], '0');

            if (empty($minor)) {
              $minor = 0;
            }
          }

          $dep = $matches[1] . '.' . $matches[2] . '.' . $minor;
        }

        if (preg_match('/([0-9])\.([0-9])([0-9]{2})?/', $base_version, $matches)) {
          $minor = 0;

          if (isset($matches[3])) {
            $minor = ltrim($matches[3], '0');

            if (empty($minor)) {
              $minor = 0;
            }
          }

          $base_version = $matches[1] . '.' . $matches[2] . '.' . $minor;
        }

        $data = [ 'provider' => $provider,
                  'app' => $app,
                  'language_id' => 1
                ];

        $info = OSCOM::callDB('Apps\GetInfo', $data, 'Site');

        if ( (count($info) > 0) && isset($info['releases'][$dep]) ) {
          $result['rpcStatus'] = RPC::STATUS_SUCCESS;

          $base_id = null;

          foreach ( $info['releases'][$dep] as $file ) {
            if ( version_compare($file['version'], $base_version, '>') ) {
              if ($legacy === true) {
                $file['version'] = $file['legacy_version'];
              }

              unset($file['legacy_version']);

              $result['app']['releases'][] = $file;
            } elseif ( $file['version'] == $base_version ) {
              $base_id = $file['id'];
            }
          }

          if ( is_numeric($base_id) && ($base_id > 0) ) {
            OSCOM::callDB('Apps\LogGetUpdates', [ 'id' => $base_id, 'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress())) ], 'Site');
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

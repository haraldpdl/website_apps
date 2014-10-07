<?php
/**
 * osCommerce Website
 *
 * @copyright Copyright (c) 2014 osCommerce; http://www.oscommerce.com
 * @license BSD License; http://www.oscommerce.com/bsdlicense.txt
 */

  namespace osCommerce\OM\Core\Site\Apps\Application\Download;

  use osCommerce\OM\Core\HTML;
  use osCommerce\OM\Core\OSCOM;

  class Controller extends \osCommerce\OM\Core\Site\Apps\ApplicationAbstract {
    protected function initialize() {
      header('X-Robots-Tag: none');

      $keys = array_keys($_GET);
      $req = array_slice($keys, array_search(OSCOM::getSiteApplication(), $keys) + 1);

      if ( count($req) >= 4 ) {
        $provider = HTML::sanitize(basename($req[0]));
        $app = HTML::sanitize(basename($req[1]));
        $dep = number_format(str_replace('_', '.', HTML::sanitize(basename($req[2]))), 3);
        $version = number_format(str_replace('_', '.', HTML::sanitize(basename($req[3]))), 3);
        $type = isset($req[4]) && ($req[4] == 'update') ? 'update' : 'full';
        $with_compress = ($type == 'update') && isset($req[5]) && ($req[5] == 'gz') ? true : false;

        $data = [ 'provider' => $provider,
                  'app' => $app,
                  'language_id' => 1
                ];

        $info = OSCOM::callDB('Apps\GetInfo', $data, 'Site');

        if ( (count($info) > 0) && isset($info['releases'][$dep]) ) {
          foreach ( $info['releases'][$dep] as $file ) {
            if ( $file['version'] == $version ) {
              if ( $type == 'update' ) {
                $filename = $version . '.phar' . ($with_compress === true ? '.gz' : '');
                $filepath = OSCOM::getConfig('dir_fs_downloads', 'Apps') . $provider . '/' . $app . '/' . $dep . '/' . $filename;

                if ( file_exists($filepath) ) {
                  $dl_filename = $provider . '-' . $app . '-' . str_replace('.', '_', $version) . '.phar' . ($with_compress === true ? '.gz' : '');

                  header('Content-Description: File Transfer');
                  header('Content-Type: application/octet-stream');
                  header('Content-Disposition: attachment; filename=' . basename($dl_filename));
                  header('Expires: 0');
                  header('Cache-Control: must-revalidate');
                  header('Pragma: public');
                  header('Content-Length: ' . filesize($filepath));

                  readfile($filepath);

                  exit;
                }
              }

              break;
            }
          }
        }
      }

      exit;
    }
  }
?>

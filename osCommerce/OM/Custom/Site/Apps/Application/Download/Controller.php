<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Download;

use osCommerce\OM\Core\{
    HTML,
    OSCOM
};

class Controller extends \osCommerce\OM\Core\Site\Apps\ApplicationAbstract
{
    protected function initialize()
    {
        header('X-Robots-Tag: none');

        $keys = array_keys($_GET);
        $req = array_slice($keys, array_search(OSCOM::getSiteApplication(), $keys) + 1);

        if (count($req) >= 3) {
            $version = null;
            $type = 'full';

            $provider = HTML::sanitize(basename($req[0]));
            $app = HTML::sanitize(basename($req[1]));
            $dep = str_replace('_', '.', HTML::sanitize(basename($req[2])));

            if (preg_match('/([0-9])\.([0-9])([0-9]{2})/', $dep, $matches)) {
                $minor = ltrim($matches[3], '0');

                if (empty($minor)) {
                    $minor = 0;
                }

                $dep = $matches[1] . '.' . $matches[2] . '.' . $minor;
            }

            if (isset($req[3])) {
                $version = str_replace('_', '.', HTML::sanitize(basename($req[3])));

                if (preg_match('/([0-9])\.([0-9])([0-9]{2})/', $version, $matches)) {
                    $minor = ltrim($matches[3], '0');

                    if (empty($minor)) {
                        $minor = 0;
                    }

                    $version = $matches[1] . '.' . $matches[2] . '.' . $minor;
                }
            }

            if (isset($req[4]) && ($req[4] == 'update')) {
                $type = 'update';
            }

            $data = [
                'provider' => $provider,
                'app' => $app,
                'language_id' => 1
            ];

            $info = OSCOM::callDB('Apps\GetInfo', $data, 'Site');

            if ((count($info) > 0) && isset($info['releases'][$dep])) {
                if (!isset($version)) {
                    foreach ($info['releases'][$dep] as $file) {
                        $version = max($version, $file['version']);
                    }
                }

                foreach ($info['releases'][$dep] as $file) {
                    if ($file['version'] == $version) {
                        $filename = $version . '-' . $type . '.zip';
                        $filepath = OSCOM::getConfig('dir_fs_downloads', 'Apps') . $provider . '/' . $app . '/' . $dep . '/' . $filename;

                        if (is_file($filepath)) {
                            OSCOM::callDB('Apps\LogDownload', [
                                'id' => $file['id'],
                                'type' => $type,
                                'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress()))
                            ], 'Site');

                            $dl_filename = $provider . '-' . $app . '-' . str_replace('.', '_', $version) . ($type == 'update' ? '-update' : '') . '.zip';

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

                        break;
                    }
                }
            }
        }

        exit;
    }
}

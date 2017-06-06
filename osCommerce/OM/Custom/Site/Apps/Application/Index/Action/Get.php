<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Index\Action;

use osCommerce\OM\Core\{
    ApplicationAbstract,
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Apps\Apps;

class Get
{
    public static function execute(ApplicationAbstract $application)
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_Template = Registry::get('Template');

        header('X-Robots-Tag: none');

        $download = [];

        if ($OSCOM_Template->valueExists('current_app')) {
            $current_app = $OSCOM_Template->getValue('current_app');

            if (isset($current_app)) {
                $addon = Apps::getAddOnInfo($current_app);

                $download = [
                    'app' => $current_app,
                    'title_slug' => $addon['title_slug'],
                    'file' => null
                ];

                $keys = array_keys($_GET);
                $req = array_slice($keys, array_search($current_app, $keys) + 1);

                if (!isset($_SESSION['Website']['Account'])) {
                    $_SESSION['login_redirect'] = [
                        'url' => OSCOM::getLink(null, 'Get', $current_app . (!empty($req) && (strlen($req[0]) === 5) ? '&' . $req[0] : '')),
                        'info_text' => $OSCOM_Template->parseContent(OSCOM::getDef('login_text_download')),
                        'cancel_url' => OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), $current_app . '&' . $download['title_slug']),
                        'cancel_text' => OSCOM::getDef('redirect_cancel_return_to_site')
                    ];

                    OSCOM::redirect(OSCOM::getLink('Website', 'Account', 'Login', 'SSL'));
                }

                if (empty($req)) {
                    $addon_files = Apps::getAddOnFiles($current_app);

                    if (!empty($addon_files)) {// && ((int)$addon['open_flag'] !== 1) || (count($addon_files) === 1)) {
                        $download['file'] = $addon_files[0]['public_id'];
                    }
                } else {
                    if (strlen($req[0]) === 5) {
                        $file = $req[0];

                        if (Apps::fileExists($current_app, $file)) {
                            $download['file'] = $file;
                        }
                    }
                }
            }
        }

        if (isset($download['file'])) {
            $public_token = isset($_POST['public_token']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['public_token'])) : '';

            if ($public_token !== md5($_SESSION['Website']['public_token'])) {
                $OSCOM_Template->setValue('initiate_download', true);
                $OSCOM_Template->setValue('download_file', $download);

                return false;
            }

            $file_path = Apps::FILES_PATH . '/' . substr($download['app'], 0, 1) . '/' . substr($download['app'], 0, 2) . '/' . $download['app'] . '-' . $download['file'] . '.zip';

            if (is_file($file_path)) {
                OSCOM::callDB('Apps\LogAddOnDownload', [
                    'user_id' => $_SESSION['Website']['Account']['id'],
                    'app_code' => $download['app'],
                    'file_code' => $download['file'],
                    'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress()))
                ], 'Site');

                $dl_filename = 'oscom-' . $download['title_slug'] . '-' . $download['app'] . '-' . $download['file'] . '.zip';

                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . $dl_filename);
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file_path));

                readfile($file_path);

                exit;
            } else {
                trigger_error('Apps\\Index\\Get - File not found: ' . $download['app'] . '-' . $download['file']);
            }
        }

        http_response_code(404);

        $OSCOM_MessageStack->add('Index', OSCOM::getDef('ms_error_file_download_nonexistent'), 'error');
    }
}

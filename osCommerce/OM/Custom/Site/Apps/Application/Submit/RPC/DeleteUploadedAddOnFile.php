<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Submit\RPC;

use osCommerce\OM\Core\OSCOM;

use osCommerce\OM\Core\Site\RPC\Controller as RPC;

use osCommerce\OM\Core\Site\Apps\Apps;

class DeleteUploadedAddOnFile
{
    public static function execute()
    {
        header('Content-Type: application/json');

        $result = [
            'rpcStatus' => RPC::STATUS_ERROR
        ];

        if (isset($_SESSION['Website']['Account'])) {
            $public_token = isset($_POST['public_token']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['public_token'])) : '';

            if ($public_token == md5($_SESSION['Website']['public_token'])) {
                if (isset($_POST['file']) && !empty($_POST['file'])) {
                    $file = basename($_POST['file']);
                    $file_path = Apps::UPLOAD_TEMP_PATH . '/' . (int)$_SESSION['Website']['Account']['id'] . '-' . $file;

                    if (is_file($file_path)) {
                        unlink($file_path);

                        $result['rpcStatus'] = RPC::STATUS_SUCCESS;
                    }
                }
            }
        }

        echo json_encode($result);
    }
}

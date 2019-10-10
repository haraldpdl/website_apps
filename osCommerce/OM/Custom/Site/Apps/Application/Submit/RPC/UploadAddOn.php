<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Submit\RPC;

use osCommerce\OM\Core\{
    Hash,
    OSCOM,
    Upload
};

use osCommerce\OM\Core\Site\RPC\Controller as RPC;

use osCommerce\OM\Core\Site\Apps\Apps;

class UploadAddOn
{
    const ERROR_LOGIN = -10;
    const ERROR_TOKEN = -20;
    const ERROR_INVALID_UPLOAD = -30;
    const ERROR_INVALID_FILE_EXTENSION = -40;
    const ERROR_FILE_SAVE = -50;
    const ERROR_IMAGE_SIZE = -60;

    public static function execute()
    {
        header('Content-Type: application/json');

        $error = null;

        if (!isset($_SESSION['Website']['Account'])) {
            $error = static::ERROR_LOGIN;
        }

        if (!isset($error)) {
            $public_token = isset($_POST['public_token']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['public_token'])) : '';

            if ($public_token !== md5($_SESSION['Website']['public_token'])) {
                $error = static::ERROR_TOKEN;
            }
        }

        if (!isset($error)) {
            if (!isset($_FILES['file']['name']) || empty($_FILES['file']['name'])) {
                $error = static::ERROR_INVALID_UPLOAD;
            }
        }

        if (!isset($error)) {
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            if (!isset($ext) || !in_array($ext, ['zip', 'png', 'jpg'])) {
                $error = static::ERROR_INVALID_FILE_EXTENSION;
            }
        }

        if (!isset($error)) {
            $file_public_id = Hash::getRandomString(5) . '.' . $ext;

            $Ufile = new Upload('file', Apps::UPLOAD_TEMP_PATH, null, null, true);
            $Ufile->setFilename((int)$_SESSION['Website']['Account']['id'] . '-' . $file_public_id);

            if ($Ufile->check()) {
                if (in_array($ext, ['png', 'jpg'])) {
                    $image = getimagesize($_FILES['file']['tmp_name']);

                    if (($image === false) || (($image[0] != Apps::COVER_IMAGE_WIDTH) && ($image[1] != Apps::COVER_IMAGE_HEIGHT)) && (($image[0] != Apps::SCREENSHOT_IMAGE_WIDTH) && ($image[1] != Apps::SCREENSHOT_IMAGE_HEIGHT))) {
                        $error = static::ERROR_IMAGE_SIZE;
                    }
                }
            } else {
                $error = static::ERROR_FILE_SAVE;
            }
        }

        if (!isset($error)) {
            $Ufile->save();
        }

        $result = [
            'rpcStatus' => $error ?? RPC::STATUS_SUCCESS
        ];

        if (isset($error)) {
            http_response_code(404);

            switch ($error) {
                case static::ERROR_LOGIN:
                    $result['error'] = OSCOM::getDef('error_upload_login_required');
                    break;

                case static::ERROR_TOKEN:
                    $result['error'] = OSCOM::getDef('error_upload_invalid_token');
                    break;

                case static::ERROR_INVALID_UPLOAD:
                    $result['error'] = OSCOM::getDef('error_upload_invalid_file');
                    break;

                case static::ERROR_INVALID_FILE_EXTENSION:
                    $result['error'] = OSCOM::getDef('error_upload_invalid_file_extension');
                    break;

                case static::ERROR_IMAGE_SIZE:
                    $result['error'] = OSCOM::getDef('error_upload_invalid_image_dimensions');
                    break;

                case static::ERROR_FILE_SAVE:
                default:
                    $result['error'] = OSCOM::getDef('error_upload_general');
            }
        } else {
            $result['filename'] = $file_public_id;
        }

        echo json_encode($result);
    }
}

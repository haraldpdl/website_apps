<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Submit\Action;

use osCommerce\OM\Core\{
    ApplicationAbstract,
    HTML,
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Apps\Apps;

class Process
{
    public static function execute(ApplicationAbstract $application)
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_Template = Registry::get('Template');

        $current_app = $OSCOM_Template->getValue('current_app');

        $errors = [];

        $public_token = isset($_POST['public_token']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['public_token'])) : '';

        if ($public_token !== md5($_SESSION['Website']['public_token'])) {
            $errors[] = OSCOM::getDef('error_form_protect_general');
        }

        if (empty($errors)) {
            $title = isset($_POST['title']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['title'])) : '';
            $short_description = isset($_POST['short_description']) ? trim($_POST['short_description']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $version = isset($_POST['version']) ? trim($_POST['version']) : '';
            $category = isset($_POST['category']) ? trim($_POST['category']) : '';
            $terms = isset($_POST['terms']) ? trim($_POST['terms']) : '';

            $title = preg_replace('/\s+/u', ' ', $title);
            $short_description = preg_replace('/\s+/u', ' ', $short_description);
            $description = preg_replace([
                '/[\r\n]{3,}/u',
                '/  +/u'
            ], [
                "\r\n\r\n",
                ' '
            ], $description);

            $uploads = [
                'zip' => isset($_POST['upload_zip']) ? basename(trim($_POST['upload_zip'])) : null,
                'cover' => isset($_POST['upload_cover']) ? basename(trim($_POST['upload_cover'])) : null,
                'images' => []
            ];

            if (isset($_POST['upload_images'])) {
                foreach (explode(',', trim($_POST['upload_images'])) as $i) {
                    $uploads['images'][] = basename(trim($i));
                }

                if (count($uploads['images']) > 7) {
                    $uploads['images'] = array_slice($uploads['images'], 0, 7);
                }
            }

            if (empty($title) || (strlen($title) < Apps::TITLE_MIN_LENGTH) || (strlen($title) > Apps::TITLE_LENGTH)) {
                $errors[] = OSCOM::getDef('ms_error_title', [
                    ':min_length' => number_format(Apps::TITLE_MIN_LENGTH),
                    ':length' => number_format(Apps::TITLE_LENGTH)
                ]);
            }

            if (empty($current_app)) {
                if (empty($short_description) || (strlen($short_description) < Apps::SHORT_DESCRIPTION_MIN_LENGTH) || (strlen($short_description) > Apps::SHORT_DESCRIPTION_LENGTH)) {
                    $errors[] = OSCOM::getDef('ms_error_short_description', [
                        ':min_length' => number_format(Apps::SHORT_DESCRIPTION_MIN_LENGTH),
                        ':length' => number_format(Apps::SHORT_DESCRIPTION_LENGTH)
                    ]);
                }
            }

            if (empty($description) || (strlen($description) < Apps::DESCRIPTION_MIN_LENGTH) || (strlen($description) > Apps::DESCRIPTION_LENGTH)) {
                $errors[] = OSCOM::getDef('ms_error_description', [
                    ':min_length' => number_format(Apps::DESCRIPTION_MIN_LENGTH),
                    ':length' => number_format(Apps::DESCRIPTION_LENGTH)
                ]);
            }

            if (empty($current_app)) {
                if (empty($version) || !Apps::isVersion($version)) {
                    $errors[] = OSCOM::getDef('ms_error_version');
                }

                if (empty($category) || !Apps::isCategory($category)) {
                    $errors[] = OSCOM::getDef('ms_error_category');
                }
            }

            if (!isset($uploads['zip'])) {
                $errors[] = OSCOM::getDef('ms_error_zip_required');
            } elseif (preg_match('/^[A-Za-z0-9]{5}\.zip$/', $uploads['zip']) === false) {
                $errors[] = OSCOM::getDef('ms_error_zip_internal_invalid_filename');
            } else {
                $zip_path = Apps::UPLOAD_TEMP_PATH . '/' . (int)$_SESSION['Website']['Account']['id'] . '-' . basename($uploads['zip']);

                if (!is_file($zip_path)) {
                    $errors[] = OSCOM::getDef('ms_error_zip_internal_nonexistant');
                } elseif (filesize($zip_path) < Apps::ZIP_FILE_MIN_SIZE) {
                    $errors[] = OSCOM::getDef('ms_error_zip_min_size', [
                        ':min_size' => number_format(Apps::ZIP_FILE_MIN_SIZE)
                    ]);
                }
            }

            if (empty($current_app)) {
                if (isset($uploads['cover'])) {
                    if (preg_match('/^[A-Za-z0-9]{5}\.(png|jpg)$/', $uploads['cover']) === false) {
                        $errors[] = OSCOM::getDef('ms_error_cover_image_internal_invalid_filename');
                    } else {
                        $cover_path = Apps::UPLOAD_TEMP_PATH . '/' . (int)$_SESSION['Website']['Account']['id'] . '-' . basename($uploads['cover']);

                        if (!is_file($cover_path)) {
                            $errors[] = OSCOM::getDef('ms_error_cover_image_internal_nonexistant');
                        } elseif (filesize($cover_path) < Apps::COVER_IMAGE_FILE_MIN_SIZE) {
                            $errors[] = OSCOM::getDef('ms_error_cover_image_min_size');
                        } else {
                            $image = getimagesize($cover_path);

                            if (($image !== false) && is_array($image) && isset($image[0]) && isset($image[1])) {
                                if (((int)$image[0] !== Apps::COVER_IMAGE_WIDTH) && ((int)$image[1] !== Apps::COVER_IMAGE_HEIGHT)) {
                                    $errors[] = OSCOM::getDef('ms_error_cover_image_invalid_dimensions', [
                                        ':width' => number_format(Apps::COVER_IMAGE_WIDTH),
                                        ':height' => number_format(Apps::COVER_IMAGE_HEIGHT)
                                    ]);
                                }
                            } else {
                                $errors[] = OSCOM::getDef('ms_error_cover_image_invalid');
                            }
                        }
                    }
                }

                if (!empty($uploads['images'])) {
                    foreach ($uploads['images'] as $i) {
                        $image_error = null;

                        if (preg_match('/^[A-Za-z0-9]{5}\.(png|jpg)$/', $i) === false) {
                            $image_error = OSCOM::getDef('ms_error_screenshot_image_internal_invalid_filename');
                        } else {
                            $images_path = Apps::UPLOAD_TEMP_PATH . '/' . (int)$_SESSION['Website']['Account']['id'] . '-' . basename($i);

                            if (!is_file($images_path)) {
                                $image_error = OSCOM::getDef('ms_error_screenshot_image_internal_nonexistant');
                            } elseif (filesize($images_path) < Apps::SCREENSHOT_IMAGE_FILE_MIN_SIZE) {
                                $image_error = OSCOM::getDef('ms_error_screenshot_image_min_size');
                            } else {
                                $image = getimagesize($images_path);

                                if (($image !== false) && is_array($image) && isset($image[0]) && isset($image[1])) {
                                    if (((int)$image[0] !== Apps::SCREENSHOT_IMAGE_WIDTH) && ((int)$image[1] !== Apps::SCREENSHOT_IMAGE_HEIGHT)) {
                                        $image_error = OSCOM::getDef('ms_error_screenshot_image_invalid_dimensions', [
                                            ':width' => number_format(Apps::SCREENSHOT_IMAGE_WIDTH),
                                            ':height' => number_format(Apps::SCREENSHOT_IMAGE_HEIGHT)
                                        ]);
                                    }
                                } else {
                                    $image_error = OSCOM::getDef('ms_error_screenshot_image_invalid');
                                }
                            }
                        }

                        if (isset($image_error)) {
                            $errors[] = $image_error;

                            break;
                        }
                    }
                }
            }

            if ($terms != '1') {
                $errors[] = OSCOM::getDef('ms_error_terms');
            }
        }

        if (empty($errors)) {
            $data = [
                'user_id' => $_SESSION['Website']['Account']['id'],
                'title' => HTML::sanitize($title),
                'description' => $description,
                'filename' => $uploads['zip']
            ];

            if (empty($current_app)) {
                $data['short_description'] = HTML::sanitize($short_description);
                $data['version_id'] = Apps::getVersionId($version);
                $data['category_id'] = Apps::getCategoryId($category);
                $data['cover_image'] = $uploads['cover'];
                $data['screenshot_images'] = !empty($uploads['images']) ? $uploads['images'] : null;
            } else {
                $data['parent_public_id'] = $current_app;
            }

            if (Apps::prepareAddOn($data)) {
                $OSCOM_MessageStack->add('Index', OSCOM::getDef('ms_success_saved_in_queue'), 'success');

                OSCOM::redirect(OSCOM::getLink(null, 'Index'));
            } else {
                $errors[] = OSCOM::getDef('ms_error_general_prepare');
            }
        }

        foreach ($errors as $e) {
            $OSCOM_MessageStack->add('Submit', $e, 'error');
        }
    }
}

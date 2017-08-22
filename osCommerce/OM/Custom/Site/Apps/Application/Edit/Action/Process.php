<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Application\Edit\Action;

use osCommerce\OM\Core\{
    ApplicationAbstract,
    HTML,
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Apps\Apps;

use osCommerce\OM\Core\Site\Website\{
    Invision,
    Users
};

class Process
{
    public static function execute(ApplicationAbstract $application)
    {
        $OSCOM_MessageStack = Registry::get('MessageStack');
        $OSCOM_Template = Registry::get('Template');

        $addon = $OSCOM_Template->getValue('addon');
        $params = $OSCOM_Template->getValue('url_params');

        $errors = [];

        $public_token = isset($_POST['public_token']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['public_token'])) : '';

        if ($public_token !== md5($_SESSION['Website']['public_token'])) {
            $errors[] = OSCOM::getDef('error_form_protect_general');
        }

        if (empty($errors)) {
            $title = isset($_POST['title']) ? trim(str_replace(array("\r\n", "\n", "\r"), '', $_POST['title'])) : '';
            $short_description = isset($_POST['short_description']) ? trim($_POST['short_description']) : '';
            $description = isset($_POST['description']) ? trim($_POST['description']) : '';
            $support_topic = isset($_POST['support_topic']) ? trim($_POST['support_topic']) : null;
            $cover_image = isset($_POST['cover_image']) ? trim($_POST['cover_image']) : null;
            $screenshot_images = isset($_POST['screenshot_images']) ? trim($_POST['screenshot_images']) : '';
            $submit_type = isset($_POST['submit_type']) ? trim($_POST['submit_type']) : '';
            $uploaders = isset($_POST['uploaders']) ? trim($_POST['uploaders']) : '';

            $title = preg_replace('/\s+/u', ' ', $title);
            $short_description = preg_replace('/\s+/u', ' ', $short_description);
            $description = preg_replace([
                '/[\r\n]{3,}/u',
                '/  +/u'
            ], [
                "\r\n\r\n",
                ' '
            ], $description);

            if (!empty($support_topic)) {
                $support_topic = json_decode($support_topic, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    if (isset($support_topic['id']) && is_numeric($support_topic['id']) && ($support_topic['id'] > 0)) {
                        if (!isset($addon['support_topic']) || !is_array($addon['support_topic']) || ($addon['support_topic'] !== $support_topic)) {
                            $topic = Invision::getMemberTopic($_SESSION['Website']['Account']['id'], $support_topic['id'], Invision::FORUM_ADDONS_CATEGORY_IDS);

                            if (!empty($topic)) {
                                $support_topic = json_encode([
                                    'id' => $topic['id'],
                                    'title' => $topic['title'] . ' (' . $topic['forum_title'] . ')',
                                    'title_seo' => $topic['title_seo']
                                ]);
                            } else {
                                $support_topic = null;
                            }
                        } else {
                            $support_topic = json_encode($support_topic);
                        }
                    } else {
                        $support_topic = null;
                    }
                } else {
                    $support_topic = null;
                }
            }

            if (empty($cover_image)) {
                $cover_image = null;
            }

            $screenshot_images = empty($screenshot_images) ? [] : explode(',', $screenshot_images);

            if (count($screenshot_images) > Apps::MAX_SCREENSHOT_IMAGES) {
                $screenshot_images = array_slice($screenshot_images, 0, Apps::MAX_SCREENSHOT_IMAGES);
            }

            $uploaders = empty($uploaders) ? [] : explode(',', $uploaders);

            if (count($uploaders) > Apps::MAX_MAINTAINERS_UPLOADERS) {
                $uploaders = array_slice($uploaders, 0, Apps::MAX_MAINTAINERS_UPLOADERS);
            }

            if (($addon['userprofile_id'] > 0) && in_array($addon['userprofile_id'], $uploaders)) {
                array_splice($uploaders, array_search($addon['userprofile_id'], $uploaders), 1);
            }

            $uploads = [
                'cover' => isset($_POST['upload_cover']) ? basename(trim($_POST['upload_cover'])) : null,
                'images' => []
            ];

            if (isset($_POST['upload_images'])) {
                foreach (explode(',', trim($_POST['upload_images'])) as $i) {
                    $uploads['images'][] = basename(trim($i));
                }

                if (count($uploads['images']) > Apps::MAX_SCREENSHOT_IMAGES) {
                    $uploads['images'] = array_slice($uploads['images'], 0, Apps::MAX_SCREENSHOT_IMAGES);
                }
            }

            if (empty($title) || (strlen($title) < Apps::TITLE_MIN_LENGTH) || (strlen($title) > Apps::TITLE_LENGTH)) {
                $errors[] = OSCOM::getDef('ms_error_title', [
                    ':min_length' => number_format(Apps::TITLE_MIN_LENGTH),
                    ':length' => number_format(Apps::TITLE_LENGTH)
                ]);
            }

            if (empty($short_description) || (strlen($short_description) < Apps::SHORT_DESCRIPTION_MIN_LENGTH) || (strlen($short_description) > Apps::SHORT_DESCRIPTION_LENGTH)) {
                $errors[] = OSCOM::getDef('ms_error_short_description', [
                    ':min_length' => number_format(Apps::SHORT_DESCRIPTION_MIN_LENGTH),
                    ':length' => number_format(Apps::SHORT_DESCRIPTION_LENGTH)
                ]);
            }

            if (empty($description) || (strlen($description) < Apps::DESCRIPTION_MIN_LENGTH) || (strlen($description) > Apps::DESCRIPTION_LENGTH)) {
                $errors[] = OSCOM::getDef('ms_error_description', [
                    ':min_length' => number_format(Apps::DESCRIPTION_MIN_LENGTH),
                    ':length' => number_format(Apps::DESCRIPTION_LENGTH)
                ]);
            }

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

            if (!empty($cover_image) && ($cover_image != $addon['cover_image'])) {
                $errors[] = OSCOM::getDef('ms_error_cover_image_original_mismatch');
            }

            if (count(array_diff($screenshot_images, $addon['screenshot_images'])) > 0) { // new images cannot be added through $screenshot_images
                $errors[] = OSCOM::getDef('ms_error_screenshot_image_original_mismatch');
            }

            if (!in_array($submit_type, ['private', 'public'])) {
                $errors[] = OSCOM::getDef('ms_error_submit_type_invalid');
            }

            $uploaders_array = [];

            foreach ($uploaders as $uid) {
                if (is_numeric($uid) && ($uid > 0) && !in_array((int)$uid, $uploaders_array)) {
                    $user = Users::get($uid);

                   if (is_array($user) && isset($user['id'])) {
                        $uploaders_array[] = $user['id'];
                    } else {
                        $errors[] = OSCOM::getDef('ms_error_user_retrieval_failed');

                        break;
                    }
                } else {
                    $errors[] = OSCOM::getDef('ms_error_uploaders_malformed');

                    break;
                }
            }
        }

        if (empty($errors)) {
            if (!empty($uploads['images'])) {
                $screenshot_images = array_merge($screenshot_images, $uploads['images']);
            }

            $data = [
                'public_id' => $addon['public_id'],
                'user_id' => $_SESSION['Website']['Account']['id'],
                'title' => HTML::sanitize($title),
                'short_description' => HTML::sanitize($short_description),
                'description' => $description,
                'support_topic' => $support_topic,
                'cover_image' => $uploads['cover'] ?? $cover_image,
                'screenshot_images' => $screenshot_images,
                'submit_type' => $submit_type,
                'uploaders' => $uploaders_array
            ];

            $result = Apps::saveAddOnInfo($data);

            if ($result === 1) {
                $OSCOM_MessageStack->add('Index', OSCOM::getDef('ms_success_saved'), 'success');
            } elseif ($result === 2) {
                $OSCOM_MessageStack->add('Index', OSCOM::getDef('ms_success_saved_in_queue'), 'success');
            }

            OSCOM::redirect(OSCOM::getLink(null, OSCOM::getDefaultSiteApplication(), implode('&', $params)));
        }

        foreach ($errors as $e) {
            $OSCOM_MessageStack->add('Edit', $e, 'error');
        }
    }
}

<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps;

use osCommerce\OM\Core\{
    AuditLog,
    Cache,
    HTML,
    OSCOM
};

use osCommerce\OM\Core\Site\Website\Users;

use Cocur\Slugify\Slugify;

class Apps
{
    const TITLE_MIN_LENGTH = 5;
    const TITLE_LENGTH = 40;
    const SHORT_DESCRIPTION_MIN_LENGTH = 20;
    const SHORT_DESCRIPTION_LENGTH = 250;
    const DESCRIPTION_MIN_LENGTH = 100;
    const DESCRIPTION_LENGTH = 4000;
    const ZIP_FILE_MIN_SIZE = 500;
    const COVER_IMAGE_FILE_MIN_SIZE = 1;
    const COVER_IMAGE_WIDTH = 320;
    const COVER_IMAGE_HEIGHT = 180;
    const SCREENSHOT_IMAGE_FILE_MIN_SIZE = 1;
    const SCREENSHOT_IMAGE_WIDTH = 1280;
    const SCREENSHOT_IMAGE_HEIGHT = 720;
    const UPLOAD_SIZE_MB = 30;
    const MAX_SCREENSHOT_IMAGES = 7;
    const MAX_MAINTAINERS_UPLOADERS = 15;

    const FILES_PATH = OSCOM::BASE_DIRECTORY . 'Custom/Site/Apps/Uploads/Files';
    const UPLOAD_TEMP_PATH = OSCOM::BASE_DIRECTORY . 'Custom/Site/Apps/Uploads/Temp';
    const UPLOAD_PENDING_PATH = OSCOM::BASE_DIRECTORY . 'Custom/Site/Apps/Uploads/Pending';

    const IMAGE_PATH = OSCOM::PUBLIC_DIRECTORY . 'public/sites/Apps/schokoladenseite';

    public static function getListing(string $category = null, string $version = null, int $pageset = null): array
    {
        $OSCOM_Cache = new Cache();

        if (isset($category) && empty($category)) {
            $category = null;
        }

        if (isset($version) && empty($version)) {
            $version = null;
        }

        if (!isset($pageset)) {
            $pageset = 1;
        }

        $cache_name = 'apps-listing';

        if (isset($category)) {
            $cache_name .= '-c' . $category;
        }

        if (isset($version)) {
            $cache_name .= '-v' . $version;
        }

        $cache_name .= '-page' . $pageset;

        if ($OSCOM_Cache->read($cache_name)) {
            $result = $OSCOM_Cache->getCache();
        } else {
            $slugify = new Slugify();

            $params = [
                'pageset' => $pageset
            ];

            if (isset($category)) {
                $params['category'] = $category;
            }

            if (isset($version)) {
                $params['version'] = $version;
            }

            $result = OSCOM::callDB('Apps\GetListing', $params, 'Site');

            if (isset($result['entries']) && !empty($result['entries'])) {
                foreach ($result['entries'] as &$r) {
                    $r['title'] = preg_replace('/\s+/u', ' ', $r['title']);
                    $r['title_slug'] = $slugify->slugify($r['title']);

                    $r['short_description'] = preg_replace('/\s+/u', ' ', $r['short_description']);
                }

                $OSCOM_Cache->write($result);
            }
        }

        return $result;
    }

    public static function getSearchListing(string $keywords, string $sort = null, string $version = null, int $pageset = null): array
    {
        if (isset($version) && empty($version)) {
            $version = null;
        }

        if (!isset($pageset)) {
            $pageset = 1;
        }

        $params = [
            'keywords' => $keywords,
            'pageset' => $pageset,
            'deep_search' => false
        ];

        if (isset($sort)) {
            $params['sort'] = $sort;
        }

        if (isset($version)) {
            $params['version'] = $version;
        }

        $result = OSCOM::callDB('Apps\GetSearchListing', $params, 'Site');

        if (isset($result['entries']) && !empty($result['entries'])) {
            $slugify = new Slugify();

            foreach ($result['entries'] as &$r) {
                $r['title'] = preg_replace('/\s+/u', ' ', $r['title']);
                $r['title_slug'] = $slugify->slugify($r['title']);

                $r['short_description'] = preg_replace('/\s+/u', ' ', $r['short_description']);
            }
        }

        return $result;
    }

    public static function getCategories(string $version = null): array
    {
        if (isset($version) && empty($version)) {
            $version = null;
        }

        $params = [];

        if (isset($version)) {
            $params['version'] = $version;
        }

        return OSCOM::callDB('Apps\GetCategories', $params, 'Site');
    }

    public static function isCategory(string $code): bool
    {
        foreach (static::getCategories() as $c) {
            if ($c['code'] == $code) {
                return true;
            }
        }

        return false;
    }

    public static function getCategoryId(string $code): int
    {
        foreach (static::getCategories() as $c) {
            if ($c['code'] == $code) {
                return $c['id'];
            }
        }

        return -1;
    }

    public static function getCategoryTitle(string $code): string
    {
        foreach (static::getCategories() as $c) {
            if ($c['code'] == $code) {
                return $c['title'];
            }
        }

        return '';
    }

    public static function getVersions(): array
    {
        return OSCOM::callDB('Apps\GetVersions', null, 'Site');
    }

    public static function isVersion(string $code): bool
    {
        foreach (static::getVersions() as $v) {
            if ($v['code'] == $code) {
                return true;
            }
        }

        return false;
    }

    public static function getVersionId(string $code): int
    {
        foreach (static::getVersions() as $v) {
            if ($v['code'] == $code) {
                return $v['id'];
            }
        }

        return -1;
    }

    public static function getVersionTitle(string $code): string
    {
        foreach (static::getVersions() as $v) {
            if ($v['code'] == $code) {
                return $v['title'];
            }
        }

        return '';
    }

    public static function exists(string $public_id): bool
    {
        $params = [
            'public_id' => $public_id,
            'strict' => true
        ];

        return OSCOM::callDB('Apps\CheckPublicId', $params, 'Site');
    }

    public static function fileExists(string $addon_public_id, string $public_id): bool
    {
        $params = [
            'addon_public_id' => $addon_public_id,
            'public_id' => $public_id,
            'strict' => true
        ];

        return OSCOM::callDB('Apps\CheckFilePublicId', $params, 'Site');
    }

    public static function getAddOnInfo(string $public_id, string $key = null)
    {
        $slugify = new Slugify();

        $params = [
            'public_id' => $public_id,
            'strict' => true
        ];

        $result = OSCOM::callDB('Apps\GetAddOnInfo', $params, 'Site');

        $result['title'] = preg_replace('/\s+/u', ' ', $result['title']);
        $result['title_slug'] = $slugify->slugify($result['title']);

        $result['short_description'] = preg_replace('/\s+/u', ' ', $result['short_description']);

        $result['description'] = preg_replace([
            '/[\r\n]{3,}/u',
            '/  +/u'
        ], [
            "\r\n\r\n",
            ' '
        ], $result['description']);

        $result['category_url'] = OSCOM::getLink(null, 'Index', 'c=' . $result['category_code']);
        $result['has_multiple_files'] = ($result['total_files'] > 1) ? true : false;

        $result['screenshot_images'] = !empty($result['screenshot_images']) ? explode(',', $result['screenshot_images']) : [];

        if (isset($key)) {
            return $result[$key];
        }

        return $result;
    }

    public static function getAddOnFiles(string $public_id): array
    {
        $params = [
            'public_id' => $public_id,
            'strict' => true
        ];

        $result = OSCOM::callDB('Apps\GetAddOnFiles', $params, 'Site');

        foreach ($result as &$r) {
            $author = [
                'name' => '',
                'formatted_name' => ''
            ];

            if (isset($r['userprofile_id']) && ((int)$r['userprofile_id'] > 0)) {
                $user = Users::get($r['userprofile_id']);

                if (is_array($user) && isset($user['name'])) {
                  $author['name'] = $user['name'];
                  $author['formatted_name'] = strip_tags($user['formatted_name']);
                }
            } elseif (!empty($r['author_name'])) {
                $author['name'] = $r['author_name'];
                $author['formatted_name'] = $r['author_name'];
            }

            $r['author'] = $author;

            unset($r['userprofile_id']);
            unset($r['author_name']);

            $r['title'] = preg_replace('/\s+/u', ' ', $r['title']);

            $r['description'] = preg_replace([
                '/[\r\n]{3,}/u',
                '/  +/u'
            ], [
                "\r\n\r\n",
                ' '
            ], $r['description']);
        }

        return $result;
    }

    public static function getAddOnAuthors(string $public_id, bool $with_names = true): array
    {
        $params = [
            'public_id' => $public_id
        ];

        $result = OSCOM::callDB('Apps\GetAddOnAuthors', $params, 'Site');

        if ($with_names === true) {
            foreach ($result as &$r) {
                $r['name'] = '';
                $r['formatted_name'] = '';

                if ((int)$r['id'] > 0) {
                    $user = Users::get($r['id']);

                    if (is_array($user) && isset($user['name'])) {
                      $r['name'] = $user['name'];
                      $r['formatted_name'] = strip_tags($user['formatted_name']);
                    }
                }
            }

            usort($result, function ($a, $b) {
                return strcasecmp($a['name'], $b['name']);
            });
        } else {
            $result = array_column($result, 'id');
        }

        return $result;
    }

    public static function deleteImageFile(string $image, string $public_id): bool
    {
        $image_path = static::IMAGE_PATH . '/' . substr($public_id, 0, 1) . '/' . substr($public_id, 0, 2) . '/' . $public_id . '-' . $image;

        if (is_file($image_path)) {
            return unlink($image_path);
        }

        trigger_error('Apps::deleteImageFile() File does not exist: ' . $image_path, E_USER_ERROR);

        return false;
    }

    public static function getAddOnId(string $public_id): int
    {
        $params = [
            'public_id' => $public_id
        ];

        $result = OSCOM::callDB('Apps\GetAddOnId', $params, 'Site');

        return $result['id'];
    }

    public static function isInQueue(string $public_id): bool
    {
        $params = [
            'public_id' => $public_id
        ];

        $result = OSCOM::callDB('Apps\IsAddOnInQueue', $params, 'Site');

        return $result === true;
    }

    public static function filterSearchKeywords(string $keywords): string
    {
        $result = '';

        $keys = [];

        if (!empty($keywords)) {
            $counter = 0;

            foreach (explode(' ', $keywords) as $k) {
                $counter ++;

                if ($counter === 6) {
                    break;
                }

                $k = HTML::sanitize($k);

                if (empty($k)) {
                    continue;
                }

                if (!in_array($k, $keys)) {
                    $keys[] = $k;
                }
            }
        }

        return implode(' ', $keys);
    }

    public static function saveAddOnInfo(array $data): int
    {
        $add_to_queue = false;
        $modified = false;

        $addon = static::getAddOnInfo($data['public_id']);
        $addon['uploaders'] = static::getAddOnAuthors($data['public_id']);

        $fields = [
            'public_id' => $data['public_id'],
            'title' => $data['title'],
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'cover_image' => $data['cover_image'],
            'screenshot_images' => $data['screenshot_images'],
            'public_flag' => ($data['submit_type'] == 'public') ? '1' : '0',
            'uploaders' => $data['uploaders']
        ];

        if (empty($fields['cover_image'])) {
            $fields['cover_image'] = null;
        }

        if ((!isset($addon['cover_image']) && isset($fields['cover_image'])) || (isset($addon['cover_image']) && isset($fields['cover_image']) && ($addon['cover_image'] != $fields['cover_image']))) {
            $add_to_queue = true;
        }

        if (count(array_diff($data['screenshot_images'], $addon['screenshot_images'])) > 0) { // new images have been uploaded
            $add_to_queue = true;
        }

        if ($add_to_queue === false) {
            if (OSCOM::callDB('Apps\SaveAddOnInfo', $fields, 'Site') === true) {
                if (isset($addon['cover_image']) && !isset($fields['cover_image'])) { // delete flagged image
                    static::deleteImageFile($addon['cover_image'], $data['public_id']);
                }

                foreach (array_diff($addon['screenshot_images'], $data['screenshot_images']) as $img) { // delete flagged images
                    static::deleteImageFile($img, $data['public_id']);
                }

                $modified = true;
            }

            if (static::saveAddOnUploaders($data['public_id'], $data['uploaders'])) {
                $modified = true;
            }
        }

        if ($add_to_queue === true) {
            $fields['user_id'] = $_SESSION['Website']['Account']['id'];

            if (static::prepareAddOn($fields)) {
                return 2;
            }
        } elseif ($modified === true) {
            static::auditLog($addon, $fields);

            Cache::clear('apps-listing');

            return 1;
        }

        return -1;
    }

    public static function prepareAddOn(array $data): bool
    {
        if (isset($data['uploaders']) && empty($data['uploaders'])) {
            $data['uploaders'] = null;
        }

        $params = [
            'title' => $data['title'],
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'],
            'user_id' => $data['user_id'],
            'versions_id' => $data['version_id'] ?? null,
            'categories_id' => $data['category_id'] ?? null,
            'zip_file' => null,
            'cover_image' => null,
            'screenshot_images' => null,
            'uploaders' => isset($data['uploaders']) ? implode(',', $data['uploaders']) : null,
            'public_id' => $data['public_id'] ?? null,
            'parent_public_id' => $data['parent_public_id'] ?? null,
            'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress()))
        ];

        if (isset($data['filename'])) {
            $params['zip_file'] = $data['filename'];

            $zip_file = static::UPLOAD_TEMP_PATH . '/' . (int)$data['user_id'] . '-' . $data['filename'];

            if (file_exists($zip_file) && copy($zip_file, static::UPLOAD_PENDING_PATH . '/' . (int)$data['user_id'] . '-' . $data['filename'])) {
                unlink($zip_file);
            }
        }

        if (isset($data['cover_image'])) {
            $params['cover_image'] = $data['cover_image'];

            $cover_image_file = static::UPLOAD_TEMP_PATH . '/' . (int)$data['user_id'] . '-' . $data['cover_image'];

            if (file_exists($cover_image_file) && copy($cover_image_file, static::UPLOAD_PENDING_PATH . '/' . (int)$data['user_id'] . '-' . $data['cover_image'])) {
                unlink($cover_image_file);
            }
        }

        if (isset($data['screenshot_images'])) {
            $params['screenshot_images'] = implode(',', $data['screenshot_images']);

            foreach ($data['screenshot_images'] as $i) {
                $i_file = static::UPLOAD_TEMP_PATH . '/' . (int)$data['user_id'] . '-' . $i;

                if (file_exists($i_file) && copy($i_file, static::UPLOAD_PENDING_PATH . '/' . (int)$data['user_id'] . '-' . $i)) {
                    unlink($i_file);
                }
            }
        }

        return OSCOM::callDB('Apps\SavePrepareAddOn', $params, 'Site');
    }

    protected static function saveAddOnUploaders(string $public_id, array $uploaders): bool
    {
        $modified = false;

        $uploaders_array = [];

        foreach ($uploaders as $u) {
            $uploaders_array[(int)$u] = 1;
        }

        $addon_id = static::getAddOnId($public_id);
        $addon_uploaders = [];

        foreach (static::getAddOnAuthors($public_id) as $au) {
            $addon_uploaders[(int)$au['id']] = 1;

            if (!array_key_exists((int)$au['id'], $uploaders_array)) {
                $uploaders_array[(int)$au['id']] = null;
            }
        }

        foreach ($uploaders_array as $uid => $ustatus) {
            if ($ustatus === 1) {
                if (!array_key_exists($uid, $addon_uploaders)) {
                    $result = OSCOM::callDB('Apps\SaveAddOnUploader', [
                        'id' => $addon_id,
                        'user_id' => $uid
                    ], 'Site');

                    if ($result === true) {
                        $modified = true;
                    }
                }
            } elseif (is_null($ustatus)) {
                $result = OSCOM::callDB('Apps\DeleteAddOnUploader', [
                    'id' => $addon_id,
                    'user_id' => $uid
                ], 'Site');

                if ($result === true) {
                    $modified = true;
                }
            }
        }

        return $modified;
    }

    protected static function auditLog(array $orig, array $new)
    {
        $public_id = $new['public_id'];
        unset($new['public_id']);

        $orig['public_flag'] = ($orig['open_flag'] == '1') ? '1' : '0';
        unset($orig['open_flag']);

        $orig['screenshot_images'] = implode(',', $orig['screenshot_images']);

        if (empty($orig['screenshot_images'])) {
            $orig['screenshot_images'] = null;
        }

        $uploaders_array = [];

        foreach ($new['uploaders'] as $u) {
            $uploaders_array[(int)$u] = (int)$u;
        }

        $addon_uploaders = [];

        foreach ($orig['uploaders'] as $u) {
            $addon_uploaders[(int)$u['id']] = (int)$u['id'];

            if (!array_key_exists((int)$u['id'], $uploaders_array)) {
                $uploaders_array[(int)$u['id']] = null;
            }
        }

        $new['uploaders'] = !empty($uploaders_array) ? $uploaders_array : null;
        $orig['uploaders'] = !empty($addon_uploaders) ? $addon_uploaders : null;

        $diff = AuditLog::getDiff($new, $orig);

/*
// new file uploads may share the same name as existing files so they are added manually to the array diff
        if (isset($new['image_small']) && ($new['image_small'] == $orig['image_small'])) {
            $diff['image_small'] = $new['image_small'];
        }

        if (isset($new['image_big']) && ($new['image_big'] == $orig['image_big'])) {
            $diff['image_big'] = $new['image_big'];
        }

        if (isset($new['image_promo']) && ($new['image_promo'] == $orig['image_promo'])) {
            $diff['image_promo'] = $new['image_promo'];
        }

        if (isset($new['banner_image']) && ($new['banner_image'] == $orig['banner_image'])) {
            $diff['banner_image'] = $new['banner_image'];
        }

        if (isset($new['carousel_image']) && ($new['carousel_image'] == $orig['carousel_image'])) {
            $diff['carousel_image'] = $new['carousel_image'];
        }
*/

        if (!empty($diff)) {
            $data = [
                'action' => 'AddOn',
                'id' => static::getAddOnId($public_id),
                'user_id' => $_SESSION['Website']['Account']['id'],
                'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress())),
                'action_type' => 'update',
                'rows' => []
            ];

            foreach ($diff as $key => $new_value) {
                if (is_array($new_value)) {
                    foreach ($new_value as $nkey => $nvalue) {
                        $data['rows'][] = [
                            'key' => $key,
                            'old' => $orig[$key][$nkey] ?? null,
                            'new' => $nvalue
                        ];
                    }
                } else {
                    $data['rows'][] = [
                        'key' => $key,
                        'old' => $orig[$key] ?? null,
                        'new' => $new_value
                    ];
                }
            }

            AuditLog::save($data);
        }
    }
}

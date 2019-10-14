<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps;

use osCommerce\OM\Core\{
    AuditLog,
    DateTime,
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
        if (isset($category) && empty($category)) {
            $category = null;
        }

        if (isset($version) && empty($version)) {
            $version = null;
        }

        if (!isset($pageset)) {
            $pageset = 1;
        }

        $cache_name = 'apps-listing-NS';

        if (isset($category)) {
            $cache_name .= '-c' . $category;
        }

        if (isset($version)) {
            $cache_name .= '-v' . $version;
        }

        $cache_name .= '-page' . $pageset;

        $CACHE_Listing = new Cache($cache_name);

        if (($result = $CACHE_Listing->get()) === false) {
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

                $CACHE_Listing->set($result);
            }
        }

        if (isset($result['entries']) && !empty($result['entries'])) {
            foreach ($result['entries'] as &$r) {
                $r['time_ago'] = DateTime::getRelative(new \DateTime($r['last_update_date']));
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

        if (isset($result['entries']) && !empty($result['entries'])) {
            foreach ($result['entries'] as &$r) {
                $r['time_ago'] = DateTime::getRelative(new \DateTime($r['last_update_date']));
            }
        }

        return $result;
    }

    public static function getCategories(string $version = null): array
    {
        if (isset($version) && empty($version)) {
            $version = null;
        }

        $cache_name = 'apps-categories';

        if (isset($version)) {
            $cache_name .= '-v' . $version;
        }

        $CACHE_Categories = new Cache($cache_name);

        if (($result = $CACHE_Categories->get()) === false) {
            $params = [];

            if (isset($version)) {
                $params['version'] = $version;
            }

            $result = OSCOM::callDB('Apps\GetCategories', $params, 'Site');

            $CACHE_Categories->set($result);
        }

        return $result;
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
        $CACHE_Versions = new Cache('apps-versions');

        if (($result = $CACHE_Versions->get()) === false) {
            $result = OSCOM::callDB('Apps\GetVersions', null, 'Site');

            $CACHE_Versions->set($result);
        }

        return $result;
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

    public static function getVersionCode(int $id): string
    {
        foreach (static::getVersions() as $v) {
            if ($v['id'] == $id) {
                return $v['code'];
            }
        }

        return '';
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

        $CACHE_Check = new Cache('apps-check-' . $public_id);

        if (($result = $CACHE_Check->get()) === false) {
            $result = OSCOM::callDB('Apps\CheckPublicId', $params, 'Site');

            if ($result === true) {
                $CACHE_Check->set($result);
            }
        }

        return $result;
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
        $CACHE_Listing = new Cache('apps-info-' . $public_id);

        if (($result = $CACHE_Listing->get()) === false) {
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

            if (!empty($result['support_topic'])) {
                $support_topic = json_decode($result['support_topic'], true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $result['support_topic'] = $support_topic;
                } else {
                    $result['support_topic'] = null;
                }
            }

            if (!empty($result['support_forum'])) {
                $support_forum = json_decode($result['support_forum'], true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $result['support_forum'] = $support_forum;
                } else {
                    $result['support_forum'] = null;
                }
            }

            $CACHE_Listing->set($result);
        }

        if (isset($key)) {
            return $result[$key];
        }

        return $result;
    }

    public static function getAddOnFiles(string $public_id): array
    {
        $CACHE_Listing = new Cache('apps-files-' . $public_id);

        if (($result = $CACHE_Listing->get()) === false) {
            $slugify = new Slugify();

            $params = [
                'public_id' => $public_id,
                'strict' => true
            ];

            $result = OSCOM::callDB('Apps\GetAddOnFiles', $params, 'Site');

            foreach ($result as &$r) {
                $author = [
                    'id' => null,
                    'name' => '',
                    'formatted_name' => '',
                    'name_slug' => ''
                ];

                if (isset($r['userprofile_id']) && ((int)$r['userprofile_id'] > 0)) {
                    $user = Users::get($r['userprofile_id']);

                    if (is_array($user) && isset($user['name'])) {
                        $author['id'] = $user['id'];
                        $author['name'] = $user['name'];
                        $author['formatted_name'] = strip_tags($user['formatted_name']);
                        $author['name_slug'] = $slugify->slugify($user['name']);
                    }
                } elseif (!empty($r['author_name'])) {
                    $author['name'] = $r['author_name'];
                    $author['formatted_name'] = strip_tags($r['author_name']);
                    $author['name_slug'] = $slugify->slugify($r['author_name']);
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

                $r['date_added_formatted'] = \DateTime::createFromFormat('Ymd', $r['date_added'])->format('jS F Y');
            }

            $CACHE_Listing->set($result);
        }

        return $result;
    }

    public static function getAddOnAuthors(string $public_id, bool $with_names = true): array
    {
        $CACHE_Listing = new Cache('apps-authors-' . $public_id);

        if (($result = $CACHE_Listing->get()) === false) {
            $params = [
                'public_id' => $public_id
            ];

            $result = OSCOM::callDB('Apps\GetAddOnAuthors', $params, 'Site');

            $CACHE_Listing->set($result);
        }

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

    public static function getPublicId(int $package_id): string
    {
        $params = [
            'id' => $package_id
        ];

        $result = OSCOM::callDB('Apps\GetAddOnPublicId', $params, 'Site');

        return $result['public_id'];
    }

    public static function getUserApps(int $user_id): array
    {
        $CACHE_Listing = new Cache('apps-user-' . $user_id . '-apps');

        if (($result = $CACHE_Listing->get()) === false) {
            $slugify = new Slugify();

            $params = [
                'user_id' => $user_id
            ];

            $result = OSCOM::callDB('Apps\GetUserApps', $params, 'Site');

            if (!is_array($result)) {
                $result = [];
            }

            foreach ($result as &$r) {
                $r['title'] = preg_replace('/\s+/u', ' ', $r['title']);
                $r['title_slug'] = $slugify->slugify($r['title']);

                $r['short_description'] = preg_replace('/\s+/u', ' ', $r['short_description']);
            }

            $CACHE_Listing->set($result, 10080);
        }

        if (!is_array($result)) {
            $result = [];
        }

        foreach ($result as &$r) {
            $r['time_ago'] = DateTime::getRelative(new \DateTime($r['last_update_date']));
        }

        return $result;
    }

    public static function getUserContributions(int $user_id): array
    {
        $CACHE_Listing = new Cache('apps-user-' . $user_id . '-contributions');

        if (($result = $CACHE_Listing->get()) === false) {
            $slugify = new Slugify();

            $params = [
                'user_id' => $user_id
            ];

            $result = OSCOM::callDB('Apps\GetUserContributions', $params, 'Site');

            if (!is_array($result)) {
                $result = [];
            }

            foreach ($result as &$r) {
                $r['title'] = preg_replace('/\s+/u', ' ', $r['title']);
                $r['title_slug'] = $slugify->slugify($r['title']);

                $r['short_description'] = preg_replace('/\s+/u', ' ', $r['short_description']);
            }

            $CACHE_Listing->set($result, 10080);
        }

        if (!is_array($result)) {
            $result = [];
        }

        foreach ($result as &$r) {
            $r['time_ago'] = DateTime::getRelative(new \DateTime($r['last_update_date']));
        }

        return $result;
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
        $keys = [];

        // filter special characters causing innodb fulltext fts_terms errors
        $keywords = str_replace([
            '+',
            '-',
            '@',
            '<',
            '>',
            '(',
            ')',
            '~',
            '*',
            '\'',
            '"'
        ], ' ', $keywords);

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

    public static function saveAddOnInfo(array $data, $bypass_queue = false): int
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
            'support_topic' => $data['support_topic'],
            'version_id' => $data['version_id'] ?? null,
            'prev_version_id' => $data['prev_version_id'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'prev_category_id' => $data['prev_category_id'] ?? null,
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

        if (($add_to_queue === false) || ($bypass_queue === true)) {
            $fields['screenshot_images'] = !empty($fields['screenshot_images']) ? implode(',', $fields['screenshot_images']) : null;

            if (isset($data['user_id'])) {
                $fields['user_id'] = $data['user_id'];
            }

            if (isset($data['ip_address'])) {
                $fields['ip_address'] = $data['ip_address'];
            }

            if (isset($data['audit_log_type'])) {
                $fields['audit_log_type'] = $data['audit_log_type'];
            }

            if (OSCOM::callDB('Apps\SaveAddOnInfo', $fields, 'Site') === true) {
                if (isset($addon['cover_image']) && (!isset($fields['cover_image']) || ($addon['cover_image'] != $fields['cover_image']))) { // delete flagged image
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

            if ($modified === true) {
                static::auditLog($addon, $fields);

                $authors = [];

                if (isset($data['user_id'])) {
                    $authors[] = $data['user_id'];
                }

                foreach (static::getAddOnFiles($data['public_id']) as $a) {
                    if (isset($a['author']['id']) && ($a['author']['id'] > 0) && !in_array($a['author']['id'], $authors)) {
                        $authors[] = $a['author']['id'];
                    }
                }

                $OSCOM_Cache = new Cache();
                $OSCOM_Cache->delete('apps-listing-NS');
                $OSCOM_Cache->delete('apps-info-' . $data['public_id']);
                $OSCOM_Cache->delete('apps-authors-' . $data['public_id']);

                $OSCOM_Cache->delete('apps-categories');

                if (isset($data['version_id'])) {
                    $OSCOM_Cache->delete('apps-categories-v' . static::getVersionCode($data['version_id']));
                }

                if (isset($data['prev_version_id'])) {
                    $OSCOM_Cache->delete('apps-categories-v' . static::getVersionCode($data['prev_version_id']));
                }

                foreach ($authors as $a) {
                    $OSCOM_Cache->delete('apps-user-' . $a . '-apps');
                    $OSCOM_Cache->delete('apps-user-' . $a . '-contributions');
                }

                return 1;
            }
        } else {
            $fields['user_id'] = $_SESSION['Website']['Account']['id'];

            if (static::prepareAddOn($fields)) {
                return 2;
            }
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
            'support_topic' => $data['support_topic'] ?? null,
            'user_id' => $data['user_id'],
            'versions_id' => $data['version_id'] ?? null,
            'prev_versions_id' => $data['prev_version_id'] ?? null,
            'categories_id' => $data['category_id'] ?? null,
            'prev_categories_id' => $data['prev_category_id'] ?? null,
            'zip_file' => null,
            'cover_image' => null,
            'screenshot_images' => null,
            'public_flag' => $data['public_flag'] ?? 0,
            'uploaders' => isset($data['uploaders']) ? implode(',', $data['uploaders']) : null,
            'public_id' => $data['public_id'] ?? null,
            'parent_public_id' => $data['parent_public_id'] ?? null,
            'ip_address' => sprintf('%u', ip2long(OSCOM::getIPAddress()))
        ];

        if (isset($data['filename'])) {
            $params['zip_file'] = $data['filename'];

            $zip_file = static::UPLOAD_TEMP_PATH . '/' . (int)$data['user_id'] . '-' . $data['filename'];

            if (is_file($zip_file) && copy($zip_file, static::UPLOAD_PENDING_PATH . '/' . (int)$data['user_id'] . '-' . $data['filename'])) {
                unlink($zip_file);
            }
        }

        if (isset($data['cover_image'])) {
            $params['cover_image'] = $data['cover_image'];

            $cover_image_file = static::UPLOAD_TEMP_PATH . '/' . (int)$data['user_id'] . '-' . $data['cover_image'];

            if (is_file($cover_image_file) && copy($cover_image_file, static::UPLOAD_PENDING_PATH . '/' . (int)$data['user_id'] . '-' . $data['cover_image'])) {
                unlink($cover_image_file);
            }
        }

        if (isset($data['screenshot_images'])) {
            $params['screenshot_images'] = implode(',', $data['screenshot_images']);

            foreach ($data['screenshot_images'] as $i) {
                $i_file = static::UPLOAD_TEMP_PATH . '/' . (int)$data['user_id'] . '-' . $i;

                if (is_file($i_file) && copy($i_file, static::UPLOAD_PENDING_PATH . '/' . (int)$data['user_id'] . '-' . $i)) {
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

        if ($modified === true) {
            $OSCOM_Cache = new Cache();
            $OSCOM_Cache->delete('apps-authors-' . $public_id);
        }

        return $modified;
    }

    protected static function auditLog(array $orig, array $new)
    {
        $action_type = $new['audit_log_type'] ?? 'update';

        if (isset($new['audit_log_type'])) {
            unset($new['audit_log_type']);
        }

        $public_id = $new['public_id'];
        unset($new['public_id']);

        $user_id = $new['user_id'] ?? $_SESSION['Website']['Account']['id'];

        if (isset($new['user_id'])) {
            unset($new['user_id']);
        }

        $ip_address = $new['ip_address'] ?? sprintf('%u', ip2long(OSCOM::getIPAddress()));

        if (isset($new['ip_address'])) {
            unset($new['ip_address']);
        }

        if (isset($orig['support_topic'])) {
            $orig['support_topic'] = json_encode($orig['support_topic']);
        }

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
                'user_id' => $user_id,
                'ip_address' => $ip_address,
                'action_type' => $action_type,
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

    public static function getPending(int $limit = null)
    {
        $params = [
            'limit' => $limit
        ];

        $result = OSCOM::callDB('Apps\GetPending', $params, 'Site');

        return $result;
    }

    public static function providerExists(string $provider): bool
    {
        $CACHE_Check = new Cache('apps-provider-' . $provider . '-check');

        if (($result = $CACHE_Check->get()) === false) {
            $params = [
                'provider' => $provider
            ];

            $result = OSCOM::callDB('Apps\CheckProvider', $params, 'Site');

            if ($result === true) {
                $CACHE_Check->set($result);
            }
        }

        return $result;
    }

    public static function getProvider(string $provider): array
    {
        $CACHE_Provider = new Cache('apps-provider-' . $provider);

        if (($result = $CACHE_Provider->get()) === false) {
            $params = [
                'provider' => $provider
            ];

            $result = OSCOM::callDB('Apps\GetProvider', $params, 'Site');

            if ($result['code'] == 'paypal') { // compatibility
                $result['code'] = 'PayPal';
            }

            $CACHE_Provider->set($result);
        }

        if (!is_array($result)) {
            $result = [];
        }

        return $result;
    }

    public static function getProviderApps(string $provider): array
    {
        $CACHE_Apps = new Cache('apps-provider-' . $provider . '-apps');

        if (($result = $CACHE_Apps->get()) === false) {
            $slugify = new Slugify();

            $params = [
                'provider' => $provider
            ];

            $result = OSCOM::callDB('Apps\GetProviderApps', $params, 'Site');

            foreach ($result as &$r) {
                $r['title'] = preg_replace('/\s+/u', ' ', $r['title']);
                $r['title_slug'] = $slugify->slugify($r['title']);

                $r['short_description'] = preg_replace('/\s+/u', ' ', $r['short_description']);
            }

            $CACHE_Apps->set($result);
        }

        if (!is_array($result)) {
            $result = [];
        }

        foreach ($result as &$r) {
            $r['time_ago'] = DateTime::getRelative(new \DateTime($r['last_update_date']));
        }

        return $result;
    }
}

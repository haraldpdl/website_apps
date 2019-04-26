<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Scripts\ProcessPendingSubmissions;

use osCommerce\OM\Core\{
    AuditLog,
    DirectoryListing,
    FileSystem,
    Hash,
    Mail,
    OSCOM,
    Registry
};

use osCommerce\OM\Core\Site\Apps\{
    Apps,
    Cache
};

use osCommerce\OM\Core\Site\Website\Users;

class ProcessPendingSubmissions implements \osCommerce\OM\Core\RunScriptInterface
{
    protected static $pdo;
    protected static $pdo_old;
    protected static $template;
    protected static $cache;

    protected static $file_check_modules;
    protected static $public_error;

    public static function execute()
    {
        OSCOM::initialize('Apps');

        static::$pdo = Registry::get('PDO');
        static::$pdo_old = Registry::get('PDO_OLD');
        static::$template = Registry::get('Template');

        static::$cache = new Cache();

        foreach (Apps::getPending(15) as $p) {
            $result = true;

            $public_id = null;

            if (empty($p['public_id']) && empty($p['parent_public_id'])) { // new app
                $public_id = static::saveNewApp($p);
            } elseif (!empty($p['parent_public_id'])) { // add file to app
                $public_id = static::saveNewFile($p);
            } elseif (!empty($p['public_id'])) { // update existing app
                $public_id = static::updateApp($p);
            }

            if (is_null($public_id) || !is_string($public_id) || empty($public_id)) {
                $result = false;
            }

            $user = Users::get($p['user_id']);
            static::$template->setValue('user_name', $user['name'], true);

            if (($result === true) && isset($public_id)) {
                $app = Apps::getAddOnInfo($public_id);

                static::$template->setValue('app_name', $app['title'], true);
                static::$template->setValue('app_url', OSCOM::getLink('Apps', 'Index', $app['public_id'] . '&' . $app['title_slug'], 'SSL', false), true);
                static::$template->setValue('app_url_raw', str_replace('&amp;', '&', OSCOM::getLink('Apps', 'Index', $app['public_id'] . '&' . $app['title_slug'], 'SSL', false)), true);

                if (empty($p['public_id']) && empty($p['parent_public_id'])) { // new app
                    $email_txt = static::$template->getContent(__DIR__ . '/pages/email_new.txt');
                    $email_html = static::$template->getContent(__DIR__ . '/pages/email_new.html');

                    if (!empty($email_txt) || !empty($email_html)) {
                        $OSCOM_Mail = new Mail($user['email'], $user['name'], 'hello@oscommerce.com', 'osCommerce', 'osCommerce App Submission');
                        $OSCOM_Mail->addBCC('hpdl@oscommerce.com', 'Harald Ponce de Leon');

                        if (!empty($email_txt)) {
                            $OSCOM_Mail->setBodyPlain($email_txt);
                        }

                        if (!empty($email_html)) {
                            $OSCOM_Mail->setBodyHTML($email_html);
                        }

                        $OSCOM_Mail->send();
                    }
                } elseif (!empty($p['parent_public_id'])) { // add file to app
                    $email_txt = static::$template->getContent(__DIR__ . '/pages/email_add.txt');
                    $email_html = static::$template->getContent(__DIR__ . '/pages/email_add.html');

                    if (!empty($email_txt) || !empty($email_html)) {
                        $OSCOM_Mail = new Mail($user['email'], $user['name'], 'hello@oscommerce.com', 'osCommerce', 'osCommerce App Update Submission');
                        $OSCOM_Mail->addBCC('hpdl@oscommerce.com', 'Harald Ponce de Leon');

                        if (!empty($email_txt)) {
                            $OSCOM_Mail->setBodyPlain($email_txt);
                        }

                        if (!empty($email_html)) {
                            $OSCOM_Mail->setBodyHTML($email_html);
                        }

                        $OSCOM_Mail->send();
                    }

                    if (isset($app['userprofile_id']) && ($app['userprofile_id'] > 0) && ($p['user_id'] != $app['userprofile_id'])) {
                        $author = Users::get($app['userprofile_id']);

                        static::$template->setValue('author_name', $author['name'], true);

                        $email_txt = static::$template->getContent(__DIR__ . '/pages/email_add_by.txt');
                        $email_html = static::$template->getContent(__DIR__ . '/pages/email_add_by.html');

                        if (!empty($email_txt) || !empty($email_html)) {
                            $OSCOM_Mail = new Mail($author['email'], $author['name'], 'hello@oscommerce.com', 'osCommerce', 'osCommerce App Update Submission');
                            $OSCOM_Mail->addBCC('hpdl@oscommerce.com', 'Harald Ponce de Leon');

                            if (!empty($email_txt)) {
                                $OSCOM_Mail->setBodyPlain($email_txt);
                            }

                            if (!empty($email_html)) {
                                $OSCOM_Mail->setBodyHTML($email_html);
                            }

                            $OSCOM_Mail->send();
                        }
                    }
                } elseif (!empty($p['public_id'])) { // update existing app
                    $email_txt = static::$template->getContent(__DIR__ . '/pages/email_update.txt');
                    $email_html = static::$template->getContent(__DIR__ . '/pages/email_update.html');

                    if (!empty($email_txt) || !empty($email_html)) {
                        $OSCOM_Mail = new Mail($user['email'], $user['name'], 'hello@oscommerce.com', 'osCommerce', 'osCommerce App Update');
                        $OSCOM_Mail->addBCC('hpdl@oscommerce.com', 'Harald Ponce de Leon');

                        if (!empty($email_txt)) {
                            $OSCOM_Mail->setBodyPlain($email_txt);
                        }

                        if (!empty($email_html)) {
                            $OSCOM_Mail->setBodyHTML($email_html);
                        }

                        $OSCOM_Mail->send();
                    }
                }
            } elseif (isset(static::$public_error) && is_array(static::$public_error) && !empty(static::$public_error)) {
                if (isset($p['public_id']) || isset($p['parent_public_id'])) {
                    $app = Apps::getAddOnInfo($p['public_id'] ?? $p['parent_public_id']);

                    static::$template->setValue('app_name', $app['title'], true);
                } else {
                    static::$template->setValue('app_name', $p['title'], true);
                }

                static::$template->setValue('error_key', static::$public_error[0], true);
                static::$template->setValue('error_message', static::$public_error[1], true);

                $email_txt = static::$template->getContent(__DIR__ . '/pages/email_error.txt');
                $email_html = static::$template->getContent(__DIR__ . '/pages/email_error.html');

                if (!empty($email_txt) || !empty($email_html)) {
                    $OSCOM_Mail = new Mail($user['email'], $user['name'], 'hello@oscommerce.com', 'osCommerce', 'osCommerce App Submission');
                    $OSCOM_Mail->addBCC('hpdl@oscommerce.com', 'Harald Ponce de Leon');

                    if (!empty($email_txt)) {
                        $OSCOM_Mail->setBodyPlain($email_txt);
                    }

                    if (!empty($email_html)) {
                        $OSCOM_Mail->setBodyHTML($email_html);
                    }

                    $OSCOM_Mail->send();
                }

                static::$public_error = null;
            }
        }
    }

    protected static function saveNewApp(array $app): ?string
    {
        $result = true;
        $public_id = null;

        $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($app['zip_file']);

        $res = static::fileCheck($file);

        if (!is_null($res)) {
            trigger_error('(Apps\ProcessPendingSubmissions::saveNewApp) File Check Failed (' . $res . '): ' . $file);

            static::$public_error = ['Zip File Package', $res];

            $result = false;
        }

        if (($result === true) && isset($app['cover_image'])) {
            $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($app['cover_image']);

            $res = static::fileCheck($file);

            if (!is_null($res)) {
                trigger_error('(Apps\ProcessPendingSubmissions::saveNewApp) File Check Cover Image Failed (' . $res . '): ' . $file);

                static::$public_error = ['Cover Image', $res];

                $result = false;
            }
        }

        if (($result === true) && !empty($app['screenshot_images'])) {
            $imgs = explode(',', $app['screenshot_images']);

            foreach ($imgs as $img) {
                $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($img);

                $res = static::fileCheck($file);

                if (!is_null($res)) {
                    trigger_error('(Apps\ProcessPendingSubmissions::saveNewApp) File Check Screenshot Failed (' . $res . '): ' . $file);

                    static::$public_error = ['Screenshot Image', $res];

                    $result = false;

                    break;
                }
            }
        }

        if ($result === true) {
            do {
                $public_id = Hash::getRandomString(5);

                $Qcheck = static::$pdo_old->get('contrib_packages', 'id', [
                    'public_id' => $public_id
                ], null, 1, [
                    'prefix_tables' => false
                ]);

                if ($Qcheck->fetch() === false) {
                    break;
                }
            } while (true);

            if (static::$pdo_old->save('contrib_packages', [
                'title' => $app['title'],
                'short_description' => $app['short_description'],
                'description' => $app['description'],
                'support_topic' => $app['support_topic'],
                'contrib_versions_id' => $app['versions_id'],
                'contrib_categories_id' => $app['categories_id'],
                'date_added' => 'now()',
                'last_update' => 'now()',
                'status' => 1,
                'download_count' => 0,
                'userprofile_id' => $app['user_id'],
                'public_flag' => 0,
                'public_id' => $public_id,
                'cover_image' => $app['cover_image'],
                'screenshot_images' => $app['screenshot_images']
            ], null, [
                'prefix_tables' => false
            ]) === 1) {
                $id = static::$pdo_old->lastInsertId();

                do {
                    $file_public_id = Hash::getRandomString(5);

                    $Qcheck = static::$pdo_old->get('contrib_files', 'id', [
                        'public_id' => $file_public_id
                    ], null, 1, [
                        'prefix_tables' => false
                    ]);

                    if ($Qcheck->fetch() === false) {
                        break;
                    }
                } while (true);

                if (static::$pdo_old->save('contrib_files', [
                    'contrib_packages_id' => $id,
                    'title' => $app['title'],
                    'description' => $app['description'],
                    'filename' => $app['zip_file'],
                    'author_name' => '',
                    'author_email_address' => '',
                    'date_added' => 'now()',
                    'status' => 1,
                    'userprofile_id' => $app['user_id'],
                    'public_id' => $file_public_id
                ], null, [
                    'prefix_tables' => false
                ]) === 1) {
                    $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($app['zip_file']);
                    $target = Apps::FILES_PATH . '/' . substr($public_id, 0, 1) . '/' . substr($public_id, 0, 2) . '/' . $public_id . '-' . $file_public_id . '.zip';

                    FileSystem::moveFile($file, $target);

                    if (isset($app['cover_image'])) {
                        $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($app['cover_image']);
                        $target = Apps::IMAGE_PATH . '/' . substr($public_id, 0, 1) . '/' . substr($public_id, 0, 2) . '/' . $public_id . '-' . basename($app['cover_image']);

                        FileSystem::moveFile($file, $target);
                    }

                    if (!empty($app['screenshot_images'])) {
                        $imgs = explode(',', $app['screenshot_images']);

                        foreach ($imgs as $img) {
                            $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($img);
                            $target = Apps::IMAGE_PATH . '/' . substr($public_id, 0, 1) . '/' . substr($public_id, 0, 2) . '/' . $public_id . '-' . basename($img);

                            FileSystem::moveFile($file, $target);
                        }
                    }

                    static::$pdo->delete('website_apps_pending', [
                        'id' => $app['id']
                    ]);

                    static::$cache->delete('apps-listing-NS');
                    static::$cache->delete('apps-user-' . (int)$app['user_id'] . '-apps');
                } else {
                    $result = false;
                }
            } else {
                $result = false;
            }
        }

        if ($result !== true) {
            static::$pdo->save('website_apps_pending', [
                'process_status' => 0
            ], [
                'id' => $app['id']
            ]);
        }

        return ($result === true) ? $public_id : null;
    }

    protected static function saveNewFile(array $app): ?string
    {
        $result = true;
        $public_id = null;

        $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($app['zip_file']);

        $res = static::fileCheck($file);

        if (!is_null($res)) {
            trigger_error('(Apps\ProcessPendingSubmissions::saveNewFile) File Check Failed (' . $res . '): ' . $file);

            static::$public_error = ['Zip File Package', $res];

            $result = false;
        }

        if ($result === true) {
            $public_id = $app['parent_public_id'];

            $id = Apps::getAddOnId($public_id);

            do {
                $file_public_id = Hash::getRandomString(5);

                $Qcheck = static::$pdo_old->get('contrib_files', 'id', [
                    'public_id' => $file_public_id
                ], null, 1, [
                    'prefix_tables' => false
                ]);

                if ($Qcheck->fetch() === false) {
                    break;
                }
            } while (true);

            if (static::$pdo_old->save('contrib_files', [
                'contrib_packages_id' => $id,
                'title' => $app['title'],
                'description' => $app['description'],
                'filename' => $app['zip_file'],
                'author_name' => '',
                'author_email_address' => '',
                'date_added' => 'now()',
                'status' => 1,
                'userprofile_id' => $app['user_id'],
                'public_id' => $file_public_id
            ], null, [
                'prefix_tables' => false
            ]) === 1) {
                $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($app['zip_file']);
                $target = Apps::FILES_PATH . '/' . substr($public_id, 0, 1) . '/' . substr($public_id, 0, 2) . '/' . $public_id . '-' . $file_public_id . '.zip';

                FileSystem::moveFile($file, $target);

                static::$pdo->delete('website_apps_pending', [
                    'id' => $app['id']
                ]);

                static::$cache->delete('apps-listing-NS');
                static::$cache->delete('apps-info-' . $public_id);
                static::$cache->delete('apps-files-' . $public_id);
                static::$cache->delete('apps-user-' . (int)$app['user_id'] . '-apps');
                static::$cache->delete('apps-user-' . (int)$app['user_id'] . '-contributions');
            } else {
                $result = false;
            }
        }

        if ($result !== true) {
            static::$pdo->save('website_apps_pending', [
                'process_status' => 0
            ], [
                'id' => $app['id']
            ]);
        }

        return ($result === true) ? $public_id : null;
    }

    protected static function updateApp(array $app): ?string
    {
        $result = true;

        $screenshot_images = !empty($app['screenshot_images']) ? explode(',', $app['screenshot_images']) : [];
        $uploaders_array = [];

        foreach (explode(',', $app['uploaders']) as $uid) {
            if (is_numeric($uid) && ($uid > 0) && !in_array((int)$uid, $uploaders_array)) {
                $user = Users::get($uid);

                if (is_array($user) && isset($user['id'])) {
                    $uploaders_array[] = $user['id'];
                }
            }
        }

        $orig = Apps::getAddOnInfo($app['public_id']);

        if (isset($app['cover_image']) && ($app['cover_image'] !== $orig['cover_image'])) {
            $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($app['cover_image']);

            $res = static::fileCheck($file);

            if (!is_null($res)) {
                trigger_error('(Apps\ProcessPendingSubmissions::updateApp) File Check Cover Image Failed (' . $res . '): ' . $file);

                static::$public_error = ['Cover Image', $res];

                $result = false;
            }
        }

        $screenshot_images_new = [];

        if ($result === true) {
            $total = count($screenshot_images);

            if ($total > Apps::MAX_SCREENSHOT_IMAGES) { // replace older screenshot images with newer images
                $to_remove = $total - Apps::MAX_SCREENSHOT_IMAGES;
                $pos_remove_old = Apps::MAX_SCREENSHOT_IMAGES - $to_remove;

                array_splice($screenshot_images, $pos_remove_old, $to_remove);
            }

            $screenshot_images_new = AuditLog::getDiff($screenshot_images, $orig['screenshot_images']);

            foreach ($screenshot_images_new as $img) {
                $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($img);

                $res = static::fileCheck($file);

                if (!is_null($res)) {
                    trigger_error('(Apps\ProcessPendingSubmissions::updateApp) File Check Screenshot Failed (' . $res . '): ' . $file);

                    static::$public_error = ['Screenshot Image', $res];

                    $result = false;

                    break;
                }
            }
        }

        if ($result === true) {
            $data = [
                'public_id' => $app['public_id'],
                'title' => $app['title'],
                'short_description' => $app['short_description'],
                'description' => $app['description'],
                'support_topic' => $app['support_topic'],
                'cover_image' => $app['cover_image'],
                'screenshot_images' => $screenshot_images,
                'public_flag' => $app['public_flag'] ?? '0',
                'user_id' => $app['user_id'],
                'ip_address' => $app['ip_address'],
                'audit_type_log' => 'process',
                'uploaders' => $uploaders_array,
                'submit_type' => ($app['public_flag'] == '1') ? 'public' : 'private'
            ];

            if (Apps::saveAddOnInfo($data, true)) {
                if (isset($app['cover_image']) && ($app['cover_image'] !== $orig['cover_image'])) {
                    $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($app['cover_image']);
                    $target = Apps::IMAGE_PATH . '/' . substr($app['public_id'], 0, 1) . '/' . substr($app['public_id'], 0, 2) . '/' . $app['public_id'] . '-' . basename($app['cover_image']);

                    FileSystem::moveFile($file, $target);
                }

                foreach ($screenshot_images_new as $img) {
                    $file = Apps::UPLOAD_PENDING_PATH . '/' . (int)$app['user_id'] . '-' . basename($img);
                    $target = Apps::IMAGE_PATH . '/' . substr($app['public_id'], 0, 1) . '/' . substr($app['public_id'], 0, 2) . '/' . $app['public_id'] . '-' . basename($img);

                    FileSystem::moveFile($file, $target);
                }

                static::$pdo->delete('website_apps_pending', [
                    'id' => $app['id']
                ]);
            } else {
                $result = false;
            }
        }

        if ($result !== true) {
            static::$pdo->save('website_apps_pending', [
                'process_status' => 0
            ], [
                'id' => $app['id']
            ]);
        }

        return ($result === true) ? $app['public_id'] : null;
    }

    protected static function fileCheck(string $file): ?string
    {
        if (!isset(static::$file_check_modules)) {
            static::$file_check_modules = [];

            $DL = new DirectoryListing(__DIR__ . '/FileChecks');
            $DL->setIncludeDirectories(false);
            $DL->setCheckExtension('php');

            foreach ($DL->getFiles() as $f) {
                $class = 'osCommerce\\OM\\Core\\Site\\Apps\\Scripts\\ProcessPendingSubmissions\\FileChecks\\' . basename($f['name'], '.php');

                if (class_exists($class) && is_subclass_of($class, 'osCommerce\\OM\\Core\\Site\\Apps\\Scripts\\ProcessPendingSubmissions\\FileChecksInterface')) {
                    $priority = isset($class::$priority) ? $class::$priority : (!empty(static::$file_check_modules) ? max(array_keys(static::$file_check_modules))+1 : 0);

                    do {
                        if (array_key_exists($priority, static::$file_check_modules)) {
                            $priority++;
                            continue;
                        }

                        static::$file_check_modules[$priority] = $class;

                        break;
                    } while (true);
                }
            }

            ksort(static::$file_check_modules);
        }

        if (is_array(static::$file_check_modules) && !empty(static::$file_check_modules)) {
            foreach (static::$file_check_modules as $class) {
                if (forward_static_call([$class, 'execute'], $file) === false) {
                    return $class::$public_fail_error ?? 'Failed';
                }
            }
        } else {
            return 'No FileCheck modules loaded.';
        }

        return null;
    }
}

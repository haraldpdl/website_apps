<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Cache;

use osCommerce\OM\Core\{
    DirectoryListing,
    FileSystem,
    OSCOM
};

class File implements \osCommerce\OM\Core\Site\Apps\CacheInterface
{
    protected $server_id;
    protected $key_prefix;

    protected $path;

    public function __construct(string $server_id)
    {
        $this->server_id = $server_id;
        $this->path = OSCOM::BASE_DIRECTORY . 'Work/Cache/' . $this->server_id . '/';
    }

    public function get($key, $default = null)
    {
        $result = $default ?? false;

        $filename = $this->path . str_replace('-', '/', $key) . '.cache';

        if (is_file($filename)) {
            $cache = file_get_contents($filename);

            if ($cache !== false) {
                $data = unserialize($cache);

                if (($data !== false) && is_array($data) && (count($data) === 2)) {
                    $expires = $data[0];

                    $difference = floor((time() - $expires) / 60);

                    if (($expires === 0) || ($difference < $expires)) {
                        $result = $data[1];
                    } else { // cache file expired
                        $this->delete($key);
                    }
                } else { // invalid cache file
                    $this->delete($key);
                }
            }
        }

        return $result;
    }

    public function set($key, $value, $ttl = null)
    {
        if (isset($ttl) && is_int($ttl)) {
            if ($ttl > 0) {
                $ttl = time() + ($ttl * 60);
            }
        } else {
            $ttl = 0;
        }

        $filename = $this->path . str_replace('-', '/', $key) . '.cache';

        $cache = [
            $ttl,
            $value
        ];

        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        return file_put_contents($filename, serialize($cache), LOCK_EX);
    }

    public function delete($key)
    {
        $result = true;

        if (substr($key, -3) == '-NS') { // delete expired namespace cache files
            $Cache_NS = new $this($this->server_id);

            $counter = $Cache_NS->get($key);

            if ($counter !== false) {
                $ns_path = $this->path . str_replace('-', '/', substr($key, 0, -3));

                $DL_CacheDir = new DirectoryListing($ns_path);
                $DL_CacheDir->setIncludeFiles(false);

                foreach ($DL_CacheDir->getFiles(false) as $f) {
                    if (preg_match('/^NS([0-9]+)$/', $f['name'], $matches) === 1) {
                        if (isset($matches[1]) && ((int)$matches[1] !== $counter)) {
                            FileSystem::rmdir($DL_CacheDir->getDirectory() . '/' . $f['name']);
                        }
                    }
                }
            }
        } else {
            $filename = $this->path . str_replace('-', '/', $key) . '.cache';

            if (is_file($filename)) {
                $result = unlink($filename);
            }

            if (is_dir(dirname($filename)) && FileSystem::isDirectoryEmpty(dirname($filename))) {
                FileSystem::rmdir(dirname($filename));
            }
        }

        return $result;
    }

    public function cleanup($key)
    {
        return $this->delete($key);
    }

    public function clear()
    {
        return FileSystem::rmdir($this->path);
    }

    public function getMultiple($keys, $default = null)
    {
        $result = [];

        foreach ($keys as $k) {
            $result[$k] = $this->get($k, $default);
        }

        return $result;
    }

    public function setMultiple($values, $ttl = null)
    {
        $result = true;

        foreach ($values as $k => $v) {
            if (!$this->set($k, $v, $ttl)) {
                $result = false;
            }
        }

        return $result;
    }

    public function deleteMultiple($keys)
    {
        $result = true;

        foreach ($keys as $k) {
            if (!$this->delete($k)) {
                $result = false;
            }
        }

        return $result;
    }

    public function has($key)
    {
        $filename = $this->path . str_replace('-', '/', $key) . '.cache';

        return is_file($filename);
    }

    public function canUse(): bool
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        return is_dir($this->path) && is_writable($this->path);
    }
}

<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2019 osCommerce; https://www.oscommerce.com
 * @license MIT; https://www.oscommerce.com/license/mit.txt
 */

namespace osCommerce\OM\Core\Site\Apps\Cache;

use osCommerce\OM\Core\OSCOM;

class Memcached implements \osCommerce\OM\Core\Site\Apps\CacheInterface
{
    protected static $links = [];
    protected $server_id;
    protected $key_prefix;

    public $fallback = 'File';

    public function __construct(string $server_id)
    {
        $this->server_id = $server_id;
    }

    public function connect()
    {
        if (!isset(static::$links[$this->server_id])) {
            if (OSCOM::configExists('memcached_servers', 'Website')) {
                $this->key_prefix = OSCOM::getConfig('memcached_key_prefix', 'Website');

                if (!empty($this->key_prefix) && (substr($this->key_prefix, -1) !== '-')) {
                    $this->key_prefix .= '-';
                }

                $servers = OSCOM::getConfig('memcached_servers', 'Website');

                if (!empty($servers)) {
                    $link = new \Memcached();

                    $link->setOption(\Memcached::OPT_PREFIX_KEY, $this->key_prefix);

                    $servers = explode(';', $servers);

                    foreach ($servers as $s) {
                        $parts = explode(':', $s, 2);

                        $link->addServer($parts[0], $parts[1] ?? 11211);
                    }

                    if ($link->getVersion() !== false) {
                        static::$links[$this->server_id] = $link;
                    }
                }
            }
        }
    }

    public function get($key, $default = null)
    {
        $result = static::$links[$this->server_id]->get($key);

        if (static::$links[$this->server_id]->getResultCode() === \Memcached::RES_NOTFOUND) {
            if (isset($default)) {
                $result = $default;
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

        return static::$links[$this->server_id]->set($key, $value, $ttl);
    }

    public function delete($key)
    {
        return static::$links[$this->server_id]->delete($key);
    }

    public function cleanup($key)
    {
        return true;
    }

    public function clear()
    {
        return static::$links[$this->server_id]->flush();
    }

    public function getMultiple($keys, $default = null)
    {
        $result = static::$links[$this->server_id]->getMulti($keys);

        foreach ($keys as $k) {
            if (!isset($result[$k])) {
                $result[$k] = $default;
            }
        }

        return $result;
    }

    public function setMultiple($values, $ttl = null)
    {
        if (isset($ttl) && is_int($ttl)) {
            if ($ttl > 0) {
                $ttl = time() + ($ttl * 60);
            }
        } else {
            $ttl = 0;
        }

        return static::$links[$this->server_id]->setMulti($values, $ttl);
    }

    public function deleteMultiple($keys)
    {
        $result = static::$links[$this->server_id]->deleteMulti($keys);

        foreach ($result as $r) {
            if ($r === \Memcached::RES_NOTFOUND) {
                return false;
            }
        }

        return true;
    }

    public function has($key)
    {
        return $this->get($key, false) !== false;
    }

    public function canUse(): bool
    {
        if (class_exists('Memcached')) {
            if (!isset(static::$links[$this->server_id])) {
                $this->connect();
            }
        }

        return isset(static::$links[$this->server_id]);
    }
}

<?php
/**
 * osCommerce Apps Marketplace Website
 *
 * @copyright (c) 2017 osCommerce; https://www.oscommerce.com
 * @license BSD; https://www.oscommerce.com/license/bsd.txt
 */

namespace osCommerce\OM\Core\Site\Apps;

use osCommerce\OM\Core\OSCOM;

class Cache
{
    protected $drivers = [
        'Memcached',
        'Files'
    ];

    protected $key;
    protected $server_id;
    protected $driver;
    protected $instance;
    protected $initattempts = 10;
    protected $locktime = 60;

    public function __construct(string $key = null, string $server_id = null, string $driver = null)
    {
        $server_id = $server_id ?? 'default';
        $driver = $driver ?? $this->drivers[0];

        $this->server_id = $server_id;
        $this->driver = $driver;

        if (isset($key)) {
            $this->setKey($key);
        }

        $this->setInstance($server_id, $driver);
    }

    protected function setInstance(string $server_id, string $driver): bool
    {
        $class = 'osCommerce\\OM\\Core\\Site\\Apps\\Cache\\' . $driver;

        if (class_exists($class)) {
            $obj = new $class($server_id);

            if ($obj instanceof \Psr\SimpleCache\CacheInterface) {
                $is_locked = false;

                $lockfile = OSCOM::BASE_DIRECTORY . 'Work/Temp/Cache' . $driver . '.lock';

                $counter = 0;

                if (is_file($lockfile)) {
                    $lockfiledate = filemtime($lockfile);

                    if (time() > ($lockfiledate + ($this->locktime * 60))) { // delete lockfile if older than 60 minutes
                        unlink($lockfile);

                        trigger_error('Cache: Clearing lockfile for \'' . $driver . '\'.');
                    } else {
                        $counter = (int)file_get_contents($lockfile);

                        if ($counter >= $this->initattempts) {
                            $is_locked = true;
                        }
                    }
                }

                if (!$is_locked && $obj->canUse()) {
                    $this->instance = $obj;

                    return true;
                } else {
                    if ($counter < $this->initattempts) {
                        $counter += 1;

                        file_put_contents($lockfile, $counter, LOCK_EX);

                        if ($counter === $this->initattempts) {
                            $lock_msg = 'Cache: \'' . $driver . '\' failed ' . $this->initattempts . ' attempts. Locking for ' . $this->locktime . ' minutes.';

                            if (isset($obj->fallback)) {
                                $lock_msg .= ' Trying \'' . $obj->fallback . '\'.';
                            }

                            trigger_error($lock_msg);
                        }
                    }

                    if (isset($obj->fallback)) {
                        if ($this->setInstance($server_id, $obj->fallback)) {
                            $this->driver = $obj->fallback;

                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    public function setKey(string $key)
    {
        if ($this->hasSafeKeyName($key)) {
            $this->key = $key;
        } else {
            trigger_error('Cache: Invalid key name (\'' . $key . '\'). Valid characters are a-zA-Z0-9-_ (and double dashes -- cannot be used)');
        }
    }

    public function hasInstance()
    {
        return isset($this->instance);
    }

    public function getInstance()
    {
        return $this->instance;
    }

    public function hasSafeKeyName(string $key = null): bool
    {
        $key ?? $this->key;

        return (strpos('--', $key) === false) && (preg_match('/^[a-zA-Z0-9-_]+$/', $key) === 1);
    }

    public function get($default = null)
    {
        if (!isset($this->key)) {
            trigger_error('Cache::get(): Key has not yet been set.');

            return $default ?? false;
        }

        if (preg_match('/^([a-zA-Z0-9-_]+\-NS)([0-9]+)?(\-[a-zA-Z0-9-_]+)?$/', $this->key, $matches) === 1) {
            if (isset($matches[2]) && (strlen($matches[2]) < 1)) {
                $Cache_NS = new $this($matches[1], $this->server_id, $this->driver);

                $counter = $Cache_NS->get();

                if ($counter === false) {
                    $counter = 0;

                    $Cache_NS->set($counter);
                }

                $this->key = $matches[1] . $counter . ($matches[3] ?? null);
            }
        }

        return $this->instance->get($this->key, $default);
    }

    public function set($value, $ttl = null)
    {
        if (!isset($this->key)) {
            trigger_error('Cache::set(): Key has not yet been set.');

            return false;
        }

        return $this->instance->set($this->key, $value, $ttl);
    }

    public function delete(string $key = null)
    {
        if (isset($key)) {
            $this->setKey($key);
        }

        if (!isset($this->key)) {
            trigger_error('Cache::delete(): Key has not yet been set.');

            return false;
        }

        if (substr($this->key, -3) == '-NS') {
            $Cache_NS = new $this($this->key, $this->server_id, $this->driver);

            $counter = $Cache_NS->get();

            $counter = ($counter === false) ? 0 : $counter + 1;

            $Cache_NS->set($counter);

            if (isset($this->instance->fallback)) {
                $Cache_NS = new $this($this->key, $this->server_id, $this->instance->fallback);

                if ($Cache_NS->get() !== false) {
                    $Cache_NS->set($counter);
                }
            }

            $this->instance->cleanup($this->key); // Some drivers can cleanup themselves (eg, File)

            return true;
        }

        return $this->instance->delete($this->key);
    }

    public function clear()
    {
        if ($this->instance->clear()) {
            if (isset($this->instance->fallback)) {
                $Cache_NS = new $this(null, $this->server_id, $this->instance->fallback);
                $Cache_NS->clear();
            }

            return true;
        }

        return false;
    }
}

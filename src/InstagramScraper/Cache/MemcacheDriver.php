<?php


namespace InstagramScraper\Cache;


use Memcached;
use Psr\Cache\CacheException;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\DateInterval;

/**
 * Class MemcacheDriver
 * @package InstagramScraper\Cache
 */
class MemcacheDriver implements CacheInterface
{
    /**
     * @var Memcached
     */
    private $driver;

    /**
     * @var MemcacheDriver
     */
    private static $instance;

    /**
     * MemcacheDriver constructor.
     */
    private function __construct()
    {
        $this->driver = new Memcached();
        $this->driver->addServer('127.0.0.1', 11211);
    }

    public static function instance():MemcacheDriver
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        return $this->driver->get($key) ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->driver->set($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        return $this->driver->delete($key);
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        return $this->driver->flush();
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        return $this->driver->getMulti($keys);
    }

    /**
     * @inheritDoc
     */
    public function setMultiple($values, $ttl = null)
    {
        // TODO: Implement setMultiple() method.
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple($keys)
    {
        // TODO: Implement deleteMultiple() method.
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return $this->driver->get($key);
    }
}
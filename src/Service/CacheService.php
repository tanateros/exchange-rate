<?php

namespace ExchangeRate\Service;

use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Helper\Psr16Adapter;

class CacheService
{
    protected $adapter;

    public function init(string $driver)
    {
        if (!file_exists(CACHE_PATH)) {
            if (!mkdir($concurrentDirectory = CACHE_PATH, 0777, true) && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        $config = new ConfigurationOption(['path' => CACHE_PATH]);
        $this->adapter = new Psr16Adapter($driver, $config);
    }

    /**
     * @return Psr16Adapter
     */
    public function getAdapter(): Psr16Adapter
    {
        return $this->adapter;
    }
}
<?php

namespace ExchangeRate\Service;

use Phpfastcache\Helper\Psr16Adapter;

class CacheService
{
    protected $adapter;

    public function init(string $driver)
    {
        $this->adapter = new Psr16Adapter($driver);
    }

    /**
     * @return Psr16Adapter
     */
    public function getAdapter(): Psr16Adapter
    {
        return $this->adapter;
    }
}
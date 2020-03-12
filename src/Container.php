<?php

namespace ExchangeRate;

use ExchangeRate\Service\CacheService;
use ExchangeRate\Service\ExchangeRateService;
use ExchangeRate\Service\RateParse\AbstractRateParseService;

/**
 * DI
 *
 * Class Container
 * @package ExchangeRate
 */
class Container
{
    /** @var ExchangeRateService $exchangeRateService */
    private static $exchangeRateService;
    /** @var CacheService $cacheService */
    private static $cacheService;

    /**
     * @param AbstractRateParseService $rateParseService
     * @return ExchangeRateService
     */
    public static function getExchangeRateService(
        AbstractRateParseService $rateParseService
    ): ExchangeRateService {
        if (null === self::$exchangeRateService) {
            self::$exchangeRateService = new ExchangeRateService($rateParseService);
        }

        return self::$exchangeRateService;
    }

    public static function getCacheService()
    {
        if (null === self::$cacheService) {
            self::$cacheService = new CacheService();
        }

        return self::$cacheService;
    }
}
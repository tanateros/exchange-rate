<?php

namespace ExchangeRate\Service;

use ExchangeRate\Container;
use ExchangeRate\Exception\CurrencyNotFoundException;
use ExchangeRate\Service\RateParse\AbstractRateParseService;

/**
 * Class ExchangeRateService
 * @package ExchangeRate\Service
 */
class ExchangeRateService
{
    const DECIMAL_LIMIT = 4;

    protected $rateParseService;

    /**
     * ExchangeRateService constructor.
     * @param AbstractRateParseService $rateParseService
     */
    public function __construct(AbstractRateParseService $rateParseService)
    {
        $this->rateParseService = $rateParseService;
    }

    /**
     * @return float
     * @throws CurrencyNotFoundException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function getRate(): float
    {
        if ($this->rateParseService->getFrom()->equals($this->rateParseService->getTo())) {
            return 1.0;
        }

        $from = $this->rateParseService->getFrom();
        $to = $this->rateParseService->getTo();
        $cache = Container::getCacheService()->getAdapter();
        $cross = $this->rateParseService->getCrossCurrency()->getName();
        $fromName = $from->getName();
        $toName = $to->getName();

        if (
            $this->hasRateInCache(DEFAULT_CURRENCY) &&
            (!$this->hasRateInCache($fromName) || !$this->hasRateInCache($toName))
        ) {
            throw new CurrencyNotFoundException();
        }

        if (
            !in_array($cross, [$fromName, $toName], true) &&
            (!$this->hasRateInCache($fromName) && !$this->hasRateInCache($toName))
        ) {
            $this->rateParseService->parseRate($this->genereteNewTtlValue(
                $this->rateParseService->getDateTime()
            ));
        }

        if (in_array($cross, [$fromName, $toName], true)) {
            if (!$this->hasRateInCache($fromName) && !$this->hasRateInCache($toName)) {
                $this->rateParseService->parseRate($this->genereteNewTtlValue(
                    $this->rateParseService->getDateTime()
                ));
            }

            if ($cross === $toName) {
                $toValue = 1;
                $fromValue = $this->getRateWithNominalFromCache($fromName);
            } else {
                $toValue = $this->getRateWithNominalFromCache($toName);
                $fromValue = 1;
            }

            return $this->calculate($toValue, $fromValue);
        }

        if (!$this->hasRateInCache($fromName) || !$this->hasRateInCache($toName)) {
            throw new CurrencyNotFoundException();
        }

        if (in_array($cross, [$fromName, $toName], true)) {
            return $this->calculate(
                $this->getRateWithNominalFromCache($toName),
                $this->getRateWithNominalFromCache($fromName)
            );
        }

        if ($cross === $toName) {
            $toValue = $this->calculate(1, $this->getRateWithNominalFromCache($toName));
            $fromValue = $this->getRateWithNominalFromCache($fromName);
        } elseif ($cross === $fromName) {
            $toValue = $cache->get($toName);
            $fromValue = $this->calculate(1, $this->getRateWithNominalFromCache($fromName));
        } else {
            $toValue = $this->calculate(1, $this->getRateWithNominalFromCache($toName));
            $fromValue = $this->calculate(1, $this->getRateWithNominalFromCache($fromName));
        }

        return $this->calculate($toValue, $fromValue);
    }

    /**
     * @return float
     * @throws CurrencyNotFoundException
     */
    public function getRateDiffYesterday(): float
    {
        $this->rateParseService->setDateTimeInYesterday();

        return $this->getRate();
    }

    /**
     * @param \DateTime $date
     * @return int
     */
    protected function genereteNewTtlValue(\DateTime $date): int
    {
        $nextDay = (clone $date)->add(new \DateInterval("P1D"));
        $prepareNextDay = new \DateTime($nextDay->format('Y-m-d') . ' 00:00:00');
        $diff = $prepareNextDay->diff($date);

        return ($diff->h * 60 * 60) + ($diff->i * 60) + $diff->s;
    }

    /**
     * @param float $from
     * @param float $to
     * @return float
     */
    protected function calculate(float $from, float $to): float
    {
        return number_format($from / $to, self::DECIMAL_LIMIT);
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function hasRateInCache(string $key): bool
    {
        return Container::getCacheService()->getAdapter()->has(
            $this->rateParseService->gePrefixWrapCacheKey() . $key
        );
    }

    /**
     * @param string $key
     * @return float|null
     */
    protected function getRateWithNominalFromCache(string $key): ?float
    {
        $cache = Container::getCacheService()->getAdapter()->get(
            $this->rateParseService->gePrefixWrapCacheKey() . $key
        );

        if (!empty($cache)) {
            $data = json_decode($cache, true);

            return $data['value'] / $data['nominal'];
        }

        return null;
    }
}

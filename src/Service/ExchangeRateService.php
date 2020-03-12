<?php

namespace ExchangeRate\Service;

use ExchangeRate\Container;
use ExchangeRate\Exception\CurrencyNotFoundException;
use ExchangeRate\Service\RateParse\AbstractRateParseService;

class ExchangeRateService
{
    const DECIMAL_LIMIT = 4;

    protected $rateParseService;

    public function __construct(AbstractRateParseService $rateParseService)
    {
        $this->rateParseService = $rateParseService;
    }

    /**
     * @return float
     * @throws CurrencyNotFoundException
     * @throws \Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException
     */
    public function getRateToday(): float
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

        if ($cache->has(DEFAULT_CURRENCY) && (!$cache->has($fromName) || !$cache->has($toName))) {
            throw new CurrencyNotFoundException();
        }

        if (
            !in_array($cross, [$fromName, $toName], true) &&
            (!$cache->has($fromName) && !$cache->has($toName))
        ) {
            $this->rateParseService->parseRate($this->genereteNewTtlValue());
        }

        if (in_array($cross, [$fromName, $toName], true)) {
            if ($cross === $toName) {
                $toValue = 1;
                $fromValue = $cache->get($fromName);
            } else {
                $toValue = $cache->get($toName);
                $fromValue = 1;
            }

            return $this->calculate($toValue, $fromValue);
        }

        if (!$cache->has($fromName) || !$cache->has($toName)) {
            throw new CurrencyNotFoundException();
        }

        if (in_array($cross, [$fromName, $toName], true)) {
            return $this->calculate($cache->get($toName), $cache->get($fromName));
        }

        if ($cross === $toName) {
            $toValue = $this->calculate(1, $cache->get($toName));
            $fromValue = $cache->get($fromName);
        } elseif ($cross === $fromName) {
            $toValue = $cache->get($toName);
            $fromValue = $this->calculate(1, $cache->get($fromName));
        } else {
            $toValue = $this->calculate(1, $cache->get($toName));
            $fromValue = $this->calculate(1, $cache->get($fromName));
        }

        return $this->calculate($toValue, $fromValue);
    }

    protected function genereteNewTtlValue(): int
    {
        $now = new \DateTime('now');
        $nextDay = (clone $now)->add(new \DateInterval("P1D"));
        $prepareNextDay = new \DateTime($nextDay->format('Y-m-d') . ' 00:00:00');
        $diff = $prepareNextDay->diff($now);

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
}

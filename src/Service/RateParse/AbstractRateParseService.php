<?php

namespace ExchangeRate\Service\RateParse;

use ExchangeRate\Entity\Currency;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractRateParseService
{
    protected $crossCurrency;
    protected $from;
    protected $to;
    protected $dateTime;

    /**
     * AbstractRateParseService constructor.
     * @param string $crossCurrency
     */
    public function __construct(string $crossCurrency)
    {
        $this->crossCurrency = new Currency($crossCurrency);
    }

    /**
     * @param Request $request
     * @return $this
     */
    public function handleRequest(Request $request): self
    {
        $this->from = new Currency($request->get('from', DEFAULT_CURRENCY));
        $this->to = new Currency($request->get('to', DEFAULT_CURRENCY));
        $this->dateTime = $this->validateStringDate($request->get('date'))
            ?? new \DateTime('now');

        return $this;
    }

    abstract public function getPrepareDate(): string;

    abstract protected function validateStringDate(string $date): ?string;

    abstract public function parseRate(int $ttl): bool;

    abstract public function gePrefixWrapCacheKey(): string;

    /**
     * @return Currency
     */
    public function getCrossCurrency(): Currency
    {
        return $this->crossCurrency;
    }

    /**
     * @return Currency
     */
    public function getFrom(): Currency
    {
        return $this->from;
    }

    /**
     * @return Currency
     */
    public function getTo(): Currency
    {
        return $this->to;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }
}
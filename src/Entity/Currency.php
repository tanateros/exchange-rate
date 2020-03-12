<?php

namespace ExchangeRate\Entity;

class Currency
{
    protected $currency;

    public function __construct(string $currency)
    {
        $this->currency = $currency;
    }

    public function equals(Currency $otherCurrency): bool
    {
        return $otherCurrency->getName() === $this->getName();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->currency;
    }
}
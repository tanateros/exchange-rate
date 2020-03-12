<?php

namespace ExchangeRate\Exception;

class CurrencyNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Currency not found.');
    }
}
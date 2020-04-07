<?php

namespace ExchangeRate\Exception;

class DiffDateTimeException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Diff start and end datetimes is bad.');
    }
}

<?php

namespace ExchangeRate\Controller;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractController
{
    protected $request;

    public function __construct()
    {
        $this->request = new Request(
            $_GET,
            $_POST,
            [],
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
    }
}

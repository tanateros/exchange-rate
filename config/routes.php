<?php

$routes = [
    '/api/cb/get-rate' => [
        'method' => 'get',
        'controller' => 'ExchangeRate\\Controller\\DefaultController',
        'action' => 'getCbRate',
        'name' => 'getCbRate',
    ],
    '/api/crypto/get-rates' => [
        'method' => 'get',
        'controller' => 'ExchangeRate\\Controller\\DefaultController',
        'action' => 'getCryptoRates',
        'name' => 'getCryptoRates',
    ],
];
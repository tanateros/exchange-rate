<?php

namespace ExchangeRate\Controller;

use ExchangeRate\Container;
use ExchangeRate\Service\RateParse\CbRateParseService;
use Pecee\SimpleRouter\SimpleRouter;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends AbstractController
{
    /**
     * @return string
     * @throws \ExchangeRate\Exception\CurrencyNotFoundException
     */
    public function getCbRate(): string
    {
        $cbRateService = new CbRateParseService(DEFAULT_CURRENCY);
        $cbRateService->handleRequest($this->request);
        $exchangeRateService = Container::getExchangeRateService($cbRateService);

        return json_encode([
            'today' => $exchangeRateService->getRate(),
            'diffYesterday' => $exchangeRateService->getRateDiffYesterday(),
        ]);
    }
}
<?php

namespace ExchangeRate\Controller;

use ExchangeRate\Component\Response\JsonResponse;
use ExchangeRate\Container;
use ExchangeRate\Service\RateParse\CbRateParseService;
use ExchangeRate\Service\RateParse\CryptoRateParseService;

class DefaultController extends AbstractController
{
    /**
     * @return string
     * @throws \ExchangeRate\Exception\CurrencyNotFoundException
     */
    public function getCbRate(): JsonResponse
    {
        $rateService = new CbRateParseService(DEFAULT_CURRENCY);
        $rateService->handleRequest($this->request);
        $exchangeRateService = Container::getExchangeRateService($rateService);

        return new JsonResponse([
            'today' => $exchangeRateService->getRate(),
            'diffYesterday' => $exchangeRateService->getRateDiffYesterday(),
        ]);
    }

    /**
     * @return JsonResponse
     * @throws \ExchangeRate\Exception\DiffDateTimeException
     */
    public function getCryptoRates(): JsonResponse
    {
        $rateService = new CryptoRateParseService(DEFAULT_CRYPTO_CURRENCY);
        $rateService->handleRequest($this->request);
        $exchangeRateService = Container::getExchangeRateService($rateService);

        return new JsonResponse($exchangeRateService->getCryptoRates());
    }
}
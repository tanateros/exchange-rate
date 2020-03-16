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
     * получение курсов, кроскурсов ЦБ.
    требование:
    - на входе: дата, код валюты, код базовой валюты (по-умолчанию RUR);
    - получать курсы с cbr.ru;
    - на выходе: значение курса и разница с предыдущим торговым днем;
    - кешировать данные cbr.ru.
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
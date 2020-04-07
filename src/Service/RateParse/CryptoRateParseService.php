<?php

namespace ExchangeRate\Service\RateParse;

use ExchangeRate\Container;
use Symfony\Component\HttpFoundation\Request;

class CryptoRateParseService extends AbstractRateParseService
{
    const RATE_URL = 'https://blockchain.info/ticker';
    const TIME_DIFF_STEP = 15;
    const ALLOW_CURRENCIES = [
        'USD',
        'EUR',
        'AUD',
        'PLN',
        'RUR',
        'JPY',
        'GBP',
    ];

    public $startDateTime;
    public $endDateTime;

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    public function getCacheKeyByDateTime(\DateTime $dateTime): string
    {
        return $dateTime->format('Y.m.d-H.i');
    }

    /**
     * @return \DateTime
     */
    public function getDateTimeByRequest(string $date): \DateTime
    {
        if ($this->dateTime instanceof \DateTime) {
            return $this->dateTime;
        }

        $dateArr = explode('/', $date);
        $dateData = explode('-', $dateArr[2]);
        $timeData = explode(':', $dateData[1]);
        $year = sprintf("%02d", $dateData[0]);
        $month = sprintf("%02d", $dateArr[1]);
        $day = sprintf("%02d", $dateArr[0]);
        $hour = sprintf("%02d", $timeData[0]);
        $minute = sprintf("%02d", $timeData[1]);

        return new \DateTime("{$year}-{$month}-{$day} {$hour}:{$minute}");
    }

    /**
     * @param int $ttl
     * @param string $cacheKey
     * @return bool
     */
    public function parseRate(int $ttl, string $cacheKey = ''): bool
    {
        try {
            $data = file_get_contents(self::RATE_URL);
            $rates = [];

            if (!empty($data)) {
                $json = json_decode($data);
            }

            foreach ($json as $key => $data) {
                if (in_array($key, self::ALLOW_CURRENCIES, true)) {
                    $rates[$key] = $data;
                }
            }

            if (!empty($rates)) {
                Container::getCacheService()->getAdapter()->set(
                    $cacheKey, json_encode($rates), $ttl + 60 * 60 * 24
                );
            }

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param string $date
     * @return string|null
     */
    protected function validateStringDate(?string $date = ''): ?string
    {
        $pattern = '/^[\d]{1,2}\/[\d]{2}\/[\d]{4}\-[\d]{2}\:[\d]{2}/';
        preg_match($pattern, $date, $matches, PREG_OFFSET_CAPTURE);

        return $matches[0][0] ?? null;
    }

    /**
     * @param \DateTime $dateTime
     * @return string
     */
    protected function prepareDateTimeString(\DateTime $dateTime): string
    {
        return $dateTime->format('d/m/Y-H:i');
    }

    /**
     * @param string $date
     * @return $this
     */
    public function handleRequest(Request $request)
    {
        $this->startDateTime = $this->validateStringDate($request->get('dateStart'))
            ?? $this->prepareDateTimeString(new \DateTime('now'));
        $this->endDateTime = $this->validateStringDate($request->get('dateEnd'))
            ?? $this->prepareDateTimeString(new \DateTime('now'));

        return $this;
    }

    /**
     * @param \DateTime $dateTime
     */
    public function correctionTime(\DateTime $dateTime): void
    {
        $startMinutes = (int)$dateTime->format('i');

        if ($startMinutes % CryptoRateParseService::TIME_DIFF_STEP > 0) {
            if ($startMinutes > 45) {
                $dateTime->sub(new \DateInterval('PT' . ($startMinutes - 45) . 'M'));
            } else if ($startMinutes > 30) {
                $dateTime->sub(new \DateInterval('PT' . ($startMinutes - 30) . 'M'));
            } else if ($startMinutes > 15) {
                $dateTime->sub(new \DateInterval('PT' . ($startMinutes - 15) . 'M'));
            } else {
                $dateTime->sub(new \DateInterval('PT' . $startMinutes . 'M'));
            }
        }
    }
}

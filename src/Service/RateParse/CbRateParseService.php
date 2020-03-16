<?php

namespace ExchangeRate\Service\RateParse;

use ExchangeRate\Container;
use Sabre\Xml\Reader;

class CbRateParseService extends AbstractRateParseService
{
    const CB_RATE_URL = 'http://www.cbr.ru/scripts/XML_daily.asp?date_req=';
    const CHAR_CODE_NODE_NAME = '{}CharCode';
    const NOMINAL_NODE_NAME = '{}Nominal';
    const VALUE_NODE_NAME = '{}Value';

    /**
     * @return string
     */
    public function getPrepareDate(): string
    {
        return $this->dateTime instanceof \DateTime
            ? $this->dateTime->format('d/m/Y')
            : $this->dateTime;
    }

    /**
     * @return \DateTime
     */
    public function getDateTime(): \DateTime
    {
        $dateArr = explode('/', $this->getPrepareDate());
        $date = $dateArr[2] . '-' . $dateArr[1] . '-' . $dateArr[0];

        return $this->dateTime instanceof \DateTime
            ? $this->dateTime
            : new \DateTime($date);
    }

    /**
     * @return $this
     */
    public function setDateTimeInYesterday(): self
    {
        $this->dateTime = $this->getDateTime()->sub(new \DateInterval("P1D"));

        return $this;
    }

    /**
     * @return string
     */
    public function setPrepareYesterdayDate(): string
    {
        return $this->dateTime instanceof \DateTime
            ? $this->dateTime->format('d/m/Y')
            : $this->dateTime;
    }

    public function parseRate(int $ttl): bool
    {
        try {
            $reader = new Reader();
            $source = file_get_contents(self::CB_RATE_URL . $this->getPrepareDate());
            $reader->xml($source);
            $data = $reader->parse();
            if (!empty($data['value'])) {
                foreach ($data['value'] as $values) {
                    $rateData = $values['value'];

                    foreach ($rateData as $key => $item) {
                        if ($item['name'] === self::CHAR_CODE_NODE_NAME) {
                            $code = $item['value'];
                        } else if ($item['name'] === self::NOMINAL_NODE_NAME) {
                            $nominal = (int)$item['value'];
                        } else if ($item['name'] === self::VALUE_NODE_NAME) {
                            $value = (float)$item['value'];
                        }
                    }

                    if (!empty($code) && !empty($value) && !empty($nominal)) {
                        Container::getCacheService()->getAdapter()->set(
                            $this->gePrefixWrapCacheKey() . $code, json_encode([
                                'value' => $value,
                                'nominal' => $nominal,
                            ]), $ttl + 60 * 60 * 24
                        );
                    }
                }
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
    protected function validateStringDate(string $date): ?string
    {
        $pattern = '/^[\d]{1,2}\/[\d]{2}\/[\d]{4}/';
        preg_match($pattern, $date, $matches, PREG_OFFSET_CAPTURE);

        return $matches[0][0] ?? null;
    }

    /**
     * @return string
     */
    public function gePrefixWrapCacheKey(): string
    {
        return str_replace('/', '.', $this->getPrepareDate()) . '-';
    }
}
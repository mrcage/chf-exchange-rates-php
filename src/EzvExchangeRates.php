<?php

namespace MrCage\EzvExchangeRates;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Retrieves exchange rates from Swiss Federal Customs Administration.
 *
 * More information https://www.ezv.admin.ch/ezv/en/home/information-companies/declaring-goods/exchange-rates--sell-.html
 */
class EzvExchangeRates
{
    const EXCHANGE_RATE_XML_TODAY = 'http://www.pwebapps.ezv.admin.ch/apps/rates/rate/getxml?activeSearchType=today';
    const EXCHANGE_RATE_XML_DATE = 'http://www.pwebapps.ezv.admin.ch/apps/rates/rate/getxml?activeSearchType=userDefinedDay&d=';

    /**
     * Retrieves the selected exchange rate for the given day (on weekends, the latest available value is used).
     *
     * @param string $currency the currency, eg. EUR or USD (case-insensitive)
     * @param Carbon\Carbon $date the date, or null to use the current day
     * @param bool $useCache if set to true, use cache to remember the value
     * @return float the exchange rate compared to the Swiss Franc (CHF)
     */
    public static function getExchangeRate(string $currency, Carbon $date = null, bool $useCache = true): float
    {
        if ($date == null || $date->isToday()) {
            $url = self::EXCHANGE_RATE_XML_TODAY;
        } else {
            $url = self::EXCHANGE_RATE_XML_DATE . $date->format('Ymd');
        }

        $fetchFunction = function () use ($url, $currency) {
            $context  = stream_context_create([
                'http' => [
                    'header' => 'Accept: application/xml',
                ],
            ]);
            $xml = file_get_contents($url, false, $context);
            $xml = simplexml_load_string($xml);
            foreach ($xml->devise as $devise) {
                if ($devise['code'] == strtolower($currency)) {
                    return (float) $devise->kurs;
                }
            }
            throw new \Exception('Unable to find current exchange rate for ' . $currency);
        };

        if (!$useCache) {
            return $fetchFunction();
        }

        // Cache results for a week, to avoid constant API calls for identical URLs
        $dateString = ($date != null ? $date : Carbon::today())->format('Ymd');
        $cacheKey = 'EzvExchangeRates:rate:' . $currency . ':' . $dateString;
        return Cache::remember($cacheKey, Carbon::now()->addWeek(1), $fetchFunction);
    }

    /**
     * Gets a list of all available currencies.
     *
     * @param bool $useCache if set to true, use cache to remember the value
     * @return array the list of currency codes as array, the key being the (uppercase) currency code, and the value the base value used for the exchange rate.
     */
    public static function listCurrencies(bool $useCache = true): array
    {
        $fetchFunction = function () {
            $context  = stream_context_create([
                'http' => [
                    'header' => 'Accept: application/xml',
                ],
            ]);
            $url = self::EXCHANGE_RATE_XML_TODAY;
            $xml = file_get_contents($url, false, $context);
            $xml = simplexml_load_string($xml);
            $currencies = [];
            foreach ($xml->devise as $devise) {
                $key = strtoupper((string) $devise['code']);
                $currencies[$key] = (int) $devise->waehrung;
            }
            ksort($currencies);
            return $currencies;
        };

        if (!$useCache) {
            return $fetchFunction();
        }

        // Cache results for a week, to avoid constant API calls for identical URLs
        $cacheKey = 'EzvExchangeRates:currencies';
        return Cache::remember($cacheKey, Carbon::now()->addWeek(1), $fetchFunction);
    }
}

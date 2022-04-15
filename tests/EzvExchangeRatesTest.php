<?php

use Carbon\Carbon;
use MrCage\EzvExchangeRates\EzvExchangeRates;
use PHPUnit\Framework\TestCase;

final class EzvExchangeRatesTest extends TestCase
{
    public function testGetExchangeRate(): void
    {
        $date = new Carbon('2020-06-28');
        $currency = 'EUR';

        $rate = EzvExchangeRates::getExchangeRate($currency, $date, false);
        $this->assertIsFloat($rate);
        $this->assertEquals(1.07474, $rate);
    }

    public function testListCurrencies(): void
    {
        $currencies = EzvExchangeRates::listCurrencies(false);
        $this->assertArrayHasKey('EUR', $currencies);
        $this->assertArrayHasKey('USD', $currencies);
        $this->assertArrayHasKey('GBP', $currencies);
    }
}

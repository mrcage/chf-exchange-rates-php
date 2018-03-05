# Swiss Franc exchange rate query library for PHP

Retrieves Swiss Franc exchange rates for foreign currencies based on data from the website of the Swiss Federal Customs Administration.

https://www.estv.admin.ch/estv/en/home/mehrwertsteuer/dienstleistungen/fremdwaehrungskurse.html

The library uses internal caching of requested data (cache time is one week).

## Usage

### Include class

    use MrCage\EzvExchangeRates\EzvExchangeRates;

### Get exchange rate for current day

    $rate = EzvExchangeRates::getExchangeRate('EUR');

### Get exchagen rate for a day in the past

The date must be specified as [Carbon](http://carbon.nesbot.com) date object.

    $rate = EzvExchangeRates::getExchangeRate('USR', Carbon::yesterday());

### Get all available currencies

    $currencies = EzvExchangeRates::listCurrencies();

This will return a list of currency codes as an array, the key being the (uppercase) currency code, and the value being the base value used for the exchange rate.

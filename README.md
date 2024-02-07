# Barikoi APIs Laravel Package

This Laravel package provides a convenient way to integrate Barikoi APIs into your Laravel applications. 

## Installation

To install the Barikoi Laravel package, you can use [Composer](https://getcomposer.org/).

```bash
composer require barikoi/barikoi-apis
```

## Configuration

After installing the package, add your Barikoi API key to your Laravel application. Open your .env file and add the following:

```php
BARIKOI_API_KEY=your_barikoi_api_key
```

# Usage
## Reverse Geocode
Get reverse geocode data by providing latitude and longitude.

```php
use Barikoi\BarikoiApis\BarikoiApiFacade as BarikoiApi;

$reverse_geo = BarikoiApi::reverseGeoCode('23.8103', '90.4125');

```

## Autocomplete
Get autocomplete data by providing a query.

```php 
use Barikoi\BarikoiApis\BarikoiApiFacade as BarikoiApi;

$autocomplete = BarikoiApi::autoComplete('dhaka');
```


## Nearby Places
Get nearby places by providing latitude, longitude, radius, and limit.
```php 
use Barikoi\BarikoiApis\BarikoiApiFacade as BarikoiApi;

$geocode = BarikoiApi::nearbyPlaces('23.8103', '90.4125', '1', '20');

```

## Rupantor
Get Rupantor data by providing a query.
```php 
use Barikoi\BarikoiApis\BarikoiApiFacade as BarikoiApi;

$rupantor = BarikoiApi::rupantor('dhaka');


```

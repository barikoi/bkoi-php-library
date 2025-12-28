# Location API Documentation

Complete documentation for all location-based services.

---

## Reverse Geocoding

Convert GPS coordinates to human-readable address.

### Method

```php
Barikoi::reverseGeocode(float $longitude, float $latitude, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `longitude` | float | Yes | Longitude coordinate (-180 to 180) |
| `latitude` | float | Yes | Latitude coordinate (-90 to 90) |
| `options` | array | No | Additional options |

### Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `district` | boolean | false | Include district name |
| `post_code` | boolean | false | Include postal code |
| `country` | boolean | false | Include country |
| `country_code` | string | `bd` | The two-letter country code (ISO Alpha-2) representing the desired country (e.g., `sa` for Saudi Arabia). You can find codes in the ISO Alpha-2 country code standard. |
| `sub_district` | boolean | false | Include sub-district |
| `union` | boolean | false | Include union |
| `pauroshova` | boolean | false | Include pauroshova (municipality) |
| `location_type` | boolean | false | Include location type |
| `division` | boolean | false | Include division name |
| `address` | boolean | false | Include full address |
| `area` | boolean | false | Include area name |
| `bangla` | boolean | false | Include Bangla address |
| `thana` | boolean | false | Include thana |

### Usage

```php
use Vendor\PackageName\Facades\Barikoi;
use Vendor\PackageName\Exceptions\BarikoiValidationException;
use Vendor\PackageName\Exceptions\BarikoiApiException;

try {
    // Basic usage (returns stdClass)
    $result = Barikoi::reverseGeocode(90.3572, 23.8067);

    // With all options
    $result = Barikoi::reverseGeocode(90.3572, 23.8067, [
        'district' => true,
        'post_code' => true,
        'country' => true,
        'country_code' => 'sa',
        'sub_district' => true,
        'union' => true,
        'pauroshova' => true,
        'location_type' => true,
        'division' => true,
        'address' => true,
        'area' => true,
        'bangla' => true,
        'thana' => true,
    ]);

    // Access response (object / stdClass)
    $address = $result->place->address;
    $district = $result->place->district;

} catch (BarikoiValidationException $e) {
    // Invalid coordinates
    echo "Error: " . $e->getMessage();
} catch (BarikoiApiException $e) {
    // API error
    echo "API Error: " . $e->getMessage();
}
```

### Response

```php
{
    "place": {
        "id": 443524,
        "distance_within_meters": 24.7438,
        "address": "Shahbagh Road, Shahbagh, Dhaka",
        "area": "Shahbagh",
        "city": "Dhaka",
        "district": "Dhaka",   // if requested
        "postCode": 1000,      // if requested
        "country_code": "bd"   // if provided
    },
    "status": 200
}
```

> **Note:** In PHP, `Barikoi::reverseGeocode()` returns a `stdClass` object that mirrors this JSON shape.  
> Access fields using `->` (for example, `$result->place->address`, `$result->status`).

### Conditions

- Longitude must be between -180 and 180
- Latitude must be between -90 and 90

### Country Code Reference

- Uses ISO Alpha-2 codes (default `BD`)
- See the country code list: [docs/country-codes.md](country-codes.md)
- For all other countries, refer to the ISO 3166-1 Alpha-2 standard.

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid coordinates (out of range) | Verify lat/lng values |
| 401 | `BarikoiApiException` | Invalid API key | Check `.env` configuration |
| 404 | `BarikoiApiException` | Location not found | Coordinates may be in ocean/invalid area |
| 429 | `BarikoiApiException` | Rate limit exceeded | Reduce request frequency |
| 500 | `BarikoiApiException` | Server error | Retry after some time |

---

## Geocoding (Rupantor)

Convert address text to GPS coordinates.

### Method

```php
Barikoi::geocode(string $address, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `address` | string | Yes | Address in Bengali or English (sent as `q` parameter) |

### Usage

```php
use Vendor\PackageName\Facades\Barikoi;
use Vendor\PackageName\Exceptions\BarikoiValidationException;
use Vendor\PackageName\Exceptions\BarikoiApiException;

try {
    // Basic usage (returns stdClass object)
    $result = Barikoi::geocode('shawrapara');

    // Access response (object / stdClass)
    $coordinates = $result->geocoded_address->geo_location;
    $latitude = $coordinates[1];
    $longitude = $coordinates[0];
    $address = $result->geocoded_address->address;

} catch (BarikoiValidationException $e) {
    echo "Invalid address: " . $e->getMessage();
} catch (BarikoiApiException $e) {
    echo "Geocoding failed: " . $e->getMessage();
}
```

### Response

```php
{
    "given_address": "shawrapara",
    "fixed_address": "shawrapara",
    "address_status": "complete",
    "confidence_score_percentage": 95,
    "status": 200,
    "geocoded_address": {
        "latitude": "23.7459408",
        "longitude": "90.37546663",
        "geo_location": [90.37546663, 23.7459408],
        "address": "Shawrapara, Mirpur, Dhaka",
        "area": "Mirpur",
        "city": "Dhaka",
        "postcode": 1216,
        "district": "Dhaka",   // if requested
        "thana": "Mirpur",     // if requested
    }
}
```

> **Note:** In PHP, `Barikoi::geocode()` returns a `stdClass` object that mirrors this JSON shape.
> Access fields using `->` (for example, `$result->geocoded_address->address`, `$result->status`).

### Conditions

- Address should be in Bangladesh
- More specific addresses give better results
- Can use Bengali or English text

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Empty or invalid address | Provide valid address string |
| 404 | `BarikoiApiException` | Address not found | Try more specific address |
| 429 | `BarikoiApiException` | Rate limit exceeded | Implement request throttling |

---

## Autocomplete

Get place suggestions as user types.

### Method

```php
Barikoi::autocomplete(string $query, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `query` | string | Yes | Search query (min 2 characters), sent as `q` |
| `options` | array | No | Supports `bangla`, `city`, `sub_area`, `sub_district` |

### Supported Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `bangla` | boolean | false | When `true`, returns Bangla text in the response (where available) |
| `city` | string | `null` | Filter results by city name (e.g., `dhaka`) |
| `sub_area` | boolean | false | Include sub-area information in results |
| `sub_district` | boolean | false | Include sub-district information in results |

### Usage

```php
try {
    // Basic autocomplete
    $suggestions = Barikoi::autocomplete('Dhaka');

    // With Bangla and city filter
    $suggestions = Barikoi::autocomplete('barikoi', [
        'bangla' => true,
        'city' => 'dhaka',
        'sub_area' => true,
        'sub_district' => true,
    ]);

    foreach ($suggestions->places as $place) {
        echo $place->address;
    }

} catch (BarikoiValidationException $e) {
    echo "Invalid query: " . $e->getMessage();
}
```

### Response

```php
{
    "places": [
        {
            "id": 3354,
            "longitude": "90.36402004477634",
            "latitude": "23.823730671721",
            "address": "Barikoi HQ (barikoi.com), Dr Mohsin Plaza, House 2/7, Begum Rokeya Sarani, Pallabi, Mirpur, Dhaka",
            "address_bn": "বাড়িকই HQ (বারিকই.কম), ডঃ মহসিন প্লাজা, বাড়ি ২/৭, বেগম রোকেয়া সরণী, পল্লবী, মিরপুর, ঢাকা, ঢাকা",
            "city": "Dhaka",
            "city_bn": "ঢাকা",
            "area": "Mirpur",
            "area_bn": "মিরপুর",
            "postCode": 1216,
            "pType": "Office",
            "subType": "Head Office",
            "district": "Dhaka",
            "uCode": "BKOI2017",
            "sub_area": "Pallabi",
            "sub_district": "Pallabi"
        }
        // ... more results
    ],
    "status": 200
}
```

> **Note:** In PHP, `Barikoi::autocomplete()` returns a `stdClass` object that mirrors this JSON shape.  
> Access fields using `->` (for example, `$suggestions->places[0]->address`, `$suggestions->status`).

### Conditions

- Minimum 2 characters required
- Maximum 100 characters
- Results limited to Bangladesh

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Query too short (< 2 chars) | Require minimum 2 characters |
| 401 | `BarikoiApiException` | Invalid API key | Check credentials |
| 429 | `BarikoiApiException` | Too many requests | Add debounce to search input |

---

## Search Place

Search for places by query string.

### Method

```php
Barikoi::searchPlace(string $query, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `query` | string | Yes | Search query |

### Usage

```php
try {
    // Basic search (returns stdClass object)
    $results = Barikoi::searchPlace('barikoi');

    // Access results (object / stdClass)
    foreach ($results->places as $place) {
        echo $place->address . ' (' . $place->place_code . ')' . PHP_EOL;
    }

    // Access session_id and status
    $sessionId = $results->session_id ?? null;
    $status = $results->status;

} catch (BarikoiApiException $e) {
    echo "Search failed: " . $e->getMessage();
}
```

### Response

```php
{
    "places": [
        {
            "address": "Barikoi HQ (barikoi.com), Dr Mohsin Plaza, House 2/7, Begum Rokeya Sarani, Pallabi, Mirpur, Dhaka",
            "place_code": "BKOI2017"
        },
        {
            "address": "Barikoi Global Map Office Rajshahi, Silicon Tower, Rajshahi Hi Tech Park, Zia Nagar, Bashuri, Rajshahi",
            "place_code": "HPADN93670"
        }
        // ... more results
    ],
    "session_id": "df365f41-602d-4211-94b9-46242947f3a0",
    "status": 200
}
```

> **Note:** In PHP, `Barikoi::searchPlace()` returns a `stdClass` object that mirrors this JSON shape.  
> Access fields using `->` (for example, `$results->places`, `$results->status`, `$results->session_id`).

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid query | Provide valid search query |
| 401 | `BarikoiApiException` | Invalid API key | Check credentials |
| 404 | `BarikoiApiException` | No results found | Try different search terms |

---

## Place Details

Get detailed information about a place using its place_code.

### Method

```php
Barikoi::placeDetails(string $placeCode, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `placeCode` | string | Yes | The place_code from search results (e.g., 'BKOI2017') |
| `options` | array | No | Optional parameters (see below) |

### Options

| Option | Type | Description |
|--------|------|-------------|
| `session_id` | string | Session ID from a previous `searchPlace()` call for better accuracy |

### Usage

```php
use Vendor\PackageName\Facades\Barikoi;
use Vendor\PackageName\Exceptions\BarikoiApiException;
use Vendor\PackageName\Exceptions\BarikoiValidationException;

try {
    // With session_id from searchPlace() for better accuracy
    $searchResults = Barikoi::searchPlace('barikoi');
    $sessionId = $searchResults->session_id ?? null;
    
    $placeDetails = Barikoi::placeDetails('BKOI2017', [
        'session_id' => $sessionId
    ]);

    // Access response (object / stdClass)
    $address = $placeDetails->place->address ?? null;
    $placeCode = $placeDetails->place->place_code ?? null;
    $latitude = $placeDetails->place->latitude ?? null;
    $longitude = $placeDetails->place->longitude ?? null;
    $sessionId = $placeDetails->session_id ?? null;
    $status = $placeDetails->status;

} catch (BarikoiApiException $e) {
    echo "API Error: " . $e->getMessage();
} catch (BarikoiValidationException $e) {
    echo "Validation Error: " . $e->getMessage();
}
```

### Response

```php
{
    "place": {
        "address": "Barikoi HQ (barikoi.com), Dr Mohsin Plaza, House 2/7, Begum Rokeya Sarani, Pallabi, Mirpur, Dhaka",
        "place_code": "BKOI2017",
        "latitude": "23.823730671721",
        "longitude": "90.36402004477634"
    },
    "session_id": "4c47157f-22d6-4689-abdf-c9f81eb43ae4",
    "status": 200
}
```

> **Note:** In PHP, `Barikoi::placeDetails()` returns a `stdClass` object that mirrors this JSON shape.  
> Access fields using `->` (for example, `$placeDetails->place->address`, `$placeDetails->status`, `$placeDetails->session_id`).

### Conditions

- Place code must be valid (obtained from `searchPlace()` or `autocomplete()` results)
- Using `session_id` from a previous `searchPlace()` call improves accuracy
- Place code format: alphanumeric string (e.g., 'BKOI2017')

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid place code | Verify place code from search results |
| 401 | `BarikoiApiException` | Invalid API key | Check credentials |
| 404 | `BarikoiApiException` | Place not found | Verify place code is correct |



---

## Nearby Search

Find places within a specified radius.

### Method

```php
Barikoi::nearby(float $longitude, float $latitude, float $distance = 0.5, int $limit = 10)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `longitude` | float | Yes | Center longitude |
| `latitude` | float | Yes | Center latitude |
| `distance` | float | No | Radius in kilometers (default: 0.5) |
| `limit` | int | No | Maximum number of results (default: 10) |


### Usage

```php
try {
    // Find places within 0.5km (default), max 10 results (default)
    // Returns stdClass object with "places" array
    $nearby = Barikoi::nearby(90.38305163, 23.87188719);

    // Find places within 1km, max 20 results
    $nearby = Barikoi::nearby(90.38305163, 23.87188719, 1.0, 20);

    // Access results (object / stdClass)
    foreach ($nearby->places as $place) {
        // API uses "Address" (capital A) and "distance_in_meters"
        echo $place->Address . ' (' . $place->distance_in_meters . 'm)' . PHP_EOL;
    }

} catch (BarikoiValidationException $e) {
    echo "Invalid parameters: " . $e->getMessage();
}
```

### Conditions

- Distance is in kilometers (e.g., 0.5 = 500 meters)
- Coordinates must be valid
- Returns nearest places first

## Snap to Road

Correct GPS coordinates to nearest road point.

### Method

```php
Barikoi::snapToRoad(float $latitude, float $longitude)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `latitude` | float | Yes | Latitude coordinate |
| `longitude` | float | Yes | Longitude coordinate |

### Usage

```php
try {
    // Snap single point to nearest road
    // Returns stdClass object with coordinates [lon, lat]
    $snapped = Barikoi::snapToRoad(23.806525320635505, 90.36129978225671);

    // Access results (object / stdClass)
    $coordinates = $snapped->coordinates ?? null;   // [lon, lat]
    $distance = $snapped->distance ?? null;         // meters
    $type = $snapped->type ?? null;                 // e.g. "Point"

} catch (BarikoiValidationException $e) {
    echo "Invalid coordinates: " . $e->getMessage();
}
```

### Response

```php
{
    "coordinates": [90.36124781587853, 23.80659275779645],
    "distance": 9.174944594219724,
    "type": "Point"
}
```

> **Note:** In PHP, `Barikoi::snapToRoad()` returns a `stdClass` object that mirrors this JSON shape.  
> Access fields using `->` (for example, `$snapped->coordinates`, `$snapped->distance`, `$snapped->type`).

### Conditions

- Accepts a single point (latitude, longitude)
- Point is sent as "latitude,longitude" format to the API
- Points should be on or near roads
- Points should be in sequence

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid coordinates | Check parameters |
| 404 | `BarikoiApiException` | No places found | Try larger radius |
| 400 | `BarikoiValidationException` | Invalid coordinates | Check latitude/longitude values |

---

## Check Nearby

Check if the current location is within a specified radius of a destination point.

### Method

```php
Barikoi::checkNearby(
    float $destinationLatitude,
    float $destinationLongitude,
    float $currentLatitude,
    float $currentLongitude,
    float $radius = 50
)
```

### Parameters

| Parameter              | Type  | Required | Description                                      |
|------------------------|-------|----------|--------------------------------------------------|
| `destinationLatitude`  | float | Yes      | Latitude of the destination (-90 to 90)          |
| `destinationLongitude` | float | Yes      | Longitude of the destination (-180 to 180)       |
| `currentLatitude`      | float | Yes      | Latitude of the current location (-90 to 90)     |
| `currentLongitude`     | float | Yes      | Longitude of the current location (-180 to 180)  |
| `radius`               | float | No       | Radius in meters (default: 50, must be positive) |

### Usage

```php
use Vendor\PackageName\Facades\Barikoi;
use Vendor\PackageName\Exceptions\BarikoiValidationException;
use Vendor\PackageName\Exceptions\BarikoiApiException;

try {
    $result = Barikoi::checkNearby(
        23.76245538673939,
        90.37852866512583,
        23.762412943322726,
        90.37864864706823,
        50
    );

    $isNearby = $result['is_nearby'] ?? false;

} catch (BarikoiValidationException $e) {
    echo "Invalid coordinates: " . $e->getMessage();
} catch (BarikoiApiException $e) {
    echo "API error: " . $e->getMessage();
}
```

### Response

```php
{
"message": "Inside geo fence",
"status": 200,
"data": {
        "id": "635085",
        "name": "Songsodh vaban",
        "radius": "55",
        "longitude": "90.37852866512583",
        "latitude": "23.76245538673939",
        "user_id": 2978
        }
}
```

### Conditions

- All latitude values must be between -90 and 90
- All longitude values must be between -180 and 180
- Radius must be a positive number

### Error Handling

| Error Code | Exception                   | Cause                        | Solution                    |
|------------|----------------------------|------------------------------|-----------------------------|
| 400        | BarikoiValidationException | Invalid coordinates or radius| Check input values          |
| 401        | BarikoiApiException        | Invalid API key              | Check credentials           |
| 404        | BarikoiApiException        | Location not found           | Verify coordinates          |
| 429        | BarikoiApiException        | Rate limit exceeded          | Reduce request frequency    |
| 500        | BarikoiApiException        | Server error                 | Retry after some time       |


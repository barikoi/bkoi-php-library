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
    // Basic usage
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

    // Access response
    $address = $result['place']['address'];
    $district = $result['place']['district'];

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
[
    'place' => [
        'id' => 443524,
        'distance_within_meters' => 24.7438,
        'address' => 'Shahbagh Road, Shahbagh, Dhaka',
        'area' => 'Shahbagh',
        'city' => 'Dhaka',
        'district' => 'Dhaka',  // if requested
        'postCode' => 1000,      // if requested
        'country_code' => 'bd',  // if provided
    ],
    'status' => 200
]
```

### Conditions

- Longitude must be between -180 and 180
- Latitude must be between -90 and 90

### Country Code Reference

- Uses ISO Alpha-2 codes (default `bd`)
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
Barikoi::geocode(string $address)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `address` | string | Yes | Address in Bengali or English (sent as `q` parameter) |

> **Note:** For the geocode API, **only** the `q` (address) parameter is supported.  
> Passing any additional options will result in a `BarikoiValidationException`.

### Usage

```php
try {
    // Basic usage (only address)
    $result = Barikoi::geocode('shawrapara');

    $coordinates = $result['geocoded_address']['geo_location'];
    $latitude = $coordinates[1];
    $longitude = $coordinates[0];

} catch (BarikoiValidationException $e) {
    echo "Invalid address: " . $e->getMessage();
} catch (BarikoiApiException $e) {
    echo "Geocoding failed: " . $e->getMessage();
}
```

### Response

```php
[
    'given_address' => 'Dhanmondi, Dhaka',
    'fixed_address' => 'dhanmondi',
    'address_status' => 'incomplete',
    'confidence_score_percentage' => 50,
    'status' => 200,
    'geocoded_address' => [
        'latitude' => '23.7459408',
        'longitude' => '90.37546663',
        'geo_location' => [90.37546663, 23.7459408],
        'address' => 'Dhanmondi, Dhanmondi, Dhaka',
        'area' => 'Dhanmondi',
        'city' => 'Dhaka',
        'postcode' => 1209,
    ]
]
```

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
| `options` | array | No | Currently only `bangla` is supported |

### Supported Options

> **Note:** For the autocomplete API, **only** the `q` parameter and the `bangla` option are supported.

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `bangla` | boolean | false | When `true`, returns Bangla text in the response (where available) |

### Usage

```php
try {
    // Basic autocomplete
    $suggestions = Barikoi::autocomplete('Dhan');

    // With Bangla results
    $suggestions = Barikoi::autocomplete('restaurant', [
        'bangla' => true,
    ]);

    foreach ($suggestions['places'] as $place) {
        echo $place['address'];
    }

} catch (BarikoiValidationException $e) {
    echo "Invalid query: " . $e->getMessage();
}
```

### Response

```php
[
    'places' => [
        [
            'id' => 123,
            'address' => 'Dhanmondi, Dhaka',
            'area' => 'Dhanmondi',
            'city' => 'Dhaka',
        ],
        // ... more results
    ],
    'status' => 200
]
```

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
| `options` | array | No | Additional options |

### Usage

```php
try {
    // Basic search
    $results = Barikoi::searchPlace('barikoi');

    // Access results
    foreach ($results['places'] as $place) {
        echo $place['address'];
    }

} catch (BarikoiApiException $e) {
    echo "Search failed: " . $e->getMessage();
}
```

### Response

```php
[
    'places' => [
        [
            'id' => 'BKOI2017',
            'address' => 'Barikoi Office, Dhaka',
            'area' => 'Dhanmondi',
            'city' => 'Dhaka',
        ],
        // ... more results
    ],
    'status' => 200
]
```

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid query | Provide valid search query |
| 401 | `BarikoiApiException` | Invalid API key | Check credentials |
| 404 | `BarikoiApiException` | No results found | Try different search terms |

---

## Nearby Search

Find places within a specified radius.

### Method

```php
Barikoi::nearby(float $longitude, float $latitude, float $distance = 0.5, int $limit = 10, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `longitude` | float | Yes | Center longitude |
| `latitude` | float | Yes | Center latitude |
| `distance` | float | No | Radius in kilometers (default: 0.5) |
| `limit` | int | No | Maximum number of results (default: 10) |
| `options` | array | No | Additional filters |

### Usage

```php
try {
    // Find places within 0.5km (default), max 10 results (default)
    $nearby = Barikoi::nearby(90.38305163, 23.87188719);

    // Find places within 1km, max 20 results
    $nearby = Barikoi::nearby(90.38305163, 23.87188719, 1.0, 20);

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
    $snapped = Barikoi::snapToRoad(23.806525320635505, 90.36129978225671);

    // Access results
    $coordinates = $snapped['coordinates'] ?? null;
    $distance = $snapped['distance'] ?? null;

} catch (BarikoiValidationException $e) {
    echo "Invalid coordinates: " . $e->getMessage();
}
```

### Response

```php
[
    'coordinates' => [
        'latitude' => 23.8065,
        'longitude' => 90.3612
    ],
    'distance' => 5.2,  // Distance in meters
    'type' => 'road'
]
```

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


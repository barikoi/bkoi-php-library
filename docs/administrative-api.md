# Administrative API Documentation

Complete documentation for Bangladesh administrative boundaries and zones.

---

## Get Divisions

Get all divisions of Bangladesh.

### Method

```php
Barikoi::administrative()->getDivisions()
```

### Parameters

None

### Usage

```php
use Vendor\PackageName\Facades\Barikoi;
use Vendor\PackageName\Exceptions\BarikoiApiException;

try {
    $divisions = Barikoi::administrative()->getDivisions();

    foreach ($divisions['places'] as $division) {
        echo $division['name'];  // Dhaka, Chattogram, etc.
    }

} catch (BarikoiApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Response

```php
[
    'places' => [
        [
            'id' => 1,
            'name' => 'Dhaka',
            'center' => '{"type":"Point","coordinates":[90.24,23.84]}'
        ],
        [
            'id' => 2,
            'name' => 'Chattogram',
            'center' => '{"type":"Point","coordinates":[91.73,22.71]}'
        ],
        // ... 8 divisions total
    ],
    'status' => 200
]
```

### Conditions

- Returns all 8 divisions of Bangladesh
- No parameters required
- Data is relatively static

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 401 | `BarikoiApiException` | Invalid API key | Check credentials |
| 500 | `BarikoiApiException` | Server error | Retry after delay |

---

## Get Districts

Get all districts or districts of a specific division.

### Method

```php
Barikoi::administrative()->getDistricts(string $division = null)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `division` | string | No | Division name (e.g., 'Dhaka') |

### Usage

```php
try {
    // All districts
    $allDistricts = Barikoi::administrative()->getDistricts();

    // Districts of Dhaka division
    $dhakaDistricts = Barikoi::administrative()->getDistricts('Dhaka');

    foreach ($dhakaDistricts['places'] as $district) {
        echo $district['name'];
        echo $district['division'];
    }

} catch (BarikoiApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Response

```php
[
    'places' => [
        [
            'id' => 1,
            'name' => 'Dhaka',
            'division' => 'Dhaka',
            'center' => '...'
        ],
        // ... more districts
    ],
    'status' => 200
]
```

### Conditions

- Total 64 districts in Bangladesh
- Division name is case-sensitive
- Returns empty if division not found

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 404 | `BarikoiApiException` | Division not found | Check division name spelling |

---

## Get Subdistricts (Upazilas)

Get subdistricts (upazilas) of a district.

### Method

```php
Barikoi::administrative()->getSubdistricts(string $district = null)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `district` | string | No | District name |

### Usage

```php
try {
    // All subdistricts
    $allSubdistricts = Barikoi::administrative()->getSubdistricts();

    // Subdistricts of Dhaka district
    $dhakaSubdistricts = Barikoi::administrative()->getSubdistricts('Dhaka');

} catch (BarikoiApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Conditions

- Total ~500 upazilas in Bangladesh
- District name is case-sensitive
- Returns empty if district not found

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 404 | `BarikoiApiException` | District not found | Verify district name |

---

## Get Thanas

Get thanas (police stations) of a district.

### Method

```php
Barikoi::administrative()->getThanas(string $district)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `district` | string | Yes | District name |

### Usage

```php
try {
    $thanas = Barikoi::administrative()->getThanas('Dhaka');

    foreach ($thanas['places'] as $thana) {
        echo $thana['name'];
        echo $thana['district'];
    }

} catch (BarikoiValidationException $e) {
    echo "District required: " . $e->getMessage();
} catch (BarikoiApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Conditions

- District parameter is required
- Returns thanas/police stations
- Name is case-sensitive

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Missing district parameter | Provide district name |
| 404 | `BarikoiApiException` | District not found | Check spelling |

---

## Get City Areas

Get areas of a city.

### Method

```php
Barikoi::administrative()->getCityAreas(string $city)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `city` | string | Yes | City name |

### Usage

```php
try {
    $areas = Barikoi::administrative()->getCityAreas('Dhaka');

    foreach ($areas['places'] as $area) {
        echo $area['name'];  // Dhanmondi, Gulshan, etc.
    }

} catch (BarikoiApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Conditions

- Major cities have detailed area data
- Returns neighborhoods/localities
- Name is case-sensitive

---

## Get Unions

Get unions of an upazila/subdistrict.

### Method

```php
Barikoi::administrative()->getUnions(string $upazila)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `upazila` | string | Yes | Upazila/subdistrict name |

### Usage

```php
try {
    $unions = Barikoi::administrative()->getUnions('Dhamrai');

    foreach ($unions['places'] as $union) {
        echo $union['name'];
        echo $union['upazila'];
    }

} catch (BarikoiApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Conditions

- Unions are rural administrative units
- Not applicable to city corporations
- Name is case-sensitive

---

## Get Areas

Get areas of a district/city.

### Method

```php
Barikoi::administrative()->getAreas(string $location)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `location` | string | Yes | District or city name |

### Usage

```php
try {
    $areas = Barikoi::administrative()->getAreas('Dhaka');

} catch (BarikoiApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

---

## Get Ward and Zone

Get ward and zone information for coordinates.

### Method

```php
Barikoi::administrative()->getWardAndZone(float $longitude, float $latitude)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `longitude` | float | Yes | Longitude coordinate |
| `latitude` | float | Yes | Latitude coordinate |

### Usage

```php
use Vendor\PackageName\Exceptions\BarikoiValidationException;

try {
    $result = Barikoi::administrative()->getWardAndZone(90.3572, 23.8067);

    $ward = $result['ward'];
    $zone = $result['zone'];

} catch (BarikoiValidationException $e) {
    echo "Invalid coordinates: " . $e->getMessage();
}
```

### Conditions

- Only works for city corporation areas
- Dhaka North/South City Corporation
- Returns null if outside city corporation

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid coordinates | Check lat/lng values |
| 404 | `BarikoiApiException` | Outside city corporation | Location has no ward/zone |

---

## Get Ward

Get ward number for coordinates.

### Method

```php
Barikoi::administrative()->getWard(float $longitude, float $latitude)
```

### Parameters

Same as getWardAndZone

### Usage

```php
try {
    $result = Barikoi::administrative()->getWard(90.3572, 23.8067);

    $wardNumber = $result['ward']['number'];
    $wardName = $result['ward']['name'];

} catch (BarikoiApiException $e) {
    echo "Ward not found: " . $e->getMessage();
}
```

### Conditions

- Only for city corporation areas
- Returns ward number and boundaries
- May return null if outside wards

---

## Get Zone

Get zone information for coordinates.

### Method

```php
Barikoi::administrative()->getZone(float $longitude, float $latitude)
```

### Parameters

Same as getWardAndZone

### Usage

```php
try {
    $result = Barikoi::administrative()->getZone(90.3572, 23.8067);

    $zoneNumber = $result['zone']['number'];
    $zoneName = $result['zone']['name'];

} catch (BarikoiApiException $e) {
    echo "Zone not found: " . $e->getMessage();
}
```

---

## Get All Ward Geometry

Get all ward boundaries with geometry data.

### Method

```php
Barikoi::administrative()->getAllWardGeometry()
```

### Parameters

None

### Usage

```php
try {
    $wards = Barikoi::administrative()->getAllWardGeometry();

    foreach ($wards['wards'] as $ward) {
        $wardId = $ward['id'];
        $geometry = $ward['geometry'];  // GeoJSON
        $boundaries = $ward['bounds'];
    }

} catch (BarikoiApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Response

```php
[
    'wards' => [
        [
            'id' => 1,
            'number' => 1,
            'name' => 'Ward 1',
            'geometry' => '...', // GeoJSON polygon
            'bounds' => [...]    // Bounding box
        ],
        // ... all wards
    ]
]
```

### Conditions

- Returns all city corporation wards
- Includes geometry boundaries
- Large response size
- Consider caching

---

## Get Ward Geometry

Get geometry for a specific ward.

### Method

```php
Barikoi::administrative()->getWardGeometry(string $wardId)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `wardId` | string | Yes | Ward identifier |

### Usage

```php
try {
    $ward = Barikoi::administrative()->getWardGeometry('ward_123');

    $geometry = $ward['geometry'];    // GeoJSON
    $boundaries = $ward['bounds'];
    $area = $ward['area_sqkm'];

} catch (BarikoiApiException $e) {
    if ($e->getCode() === 404) {
        echo "Ward not found";
    }
}
```

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid ward ID | Check ward ID format |
| 404 | `BarikoiApiException` | Ward not found | Verify ward exists |

---

## Get Zones

Get all zones.

### Method

```php
Barikoi::administrative()->getZones()
```

### Usage

```php
try {
    $zones = Barikoi::administrative()->getZones();

    foreach ($zones['zones'] as $zone) {
        echo $zone['name'];
        echo $zone['city_corporation'];
    }

} catch (BarikoiApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

---

## Get City Corporation

Get city corporation information for coordinates.

### Method

```php
Barikoi::administrative()->getCityCorporation(float $longitude, float $latitude)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `longitude` | float | Yes | Longitude coordinate |
| `latitude` | float | Yes | Latitude coordinate |

### Usage

```php
try {
    $result = Barikoi::administrative()->getCityCorporation(90.3572, 23.8067);

    $corpName = $result['name'];        // "Dhaka North" or "Dhaka South"
    $ward = $result['ward'];
    $zone = $result['zone'];

} catch (BarikoiApiException $e) {
    if ($e->getCode() === 404) {
        echo "Outside city corporation area";
    }
}
```

### Conditions

- Only works within city corporation boundaries
- Returns Dhaka North, Dhaka South, Chattogram, etc.
- Null if in non-corporation area

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid coordinates | Validate lat/lng |
| 404 | `BarikoiApiException` | Outside city corporation | Return null or default |

---

## Caching Recommendations

Administrative data is relatively static. Consider caching:

```php
use Illuminate\Support\Facades\Cache;

// Cache divisions for 24 hours
$divisions = Cache::remember('barikoi_divisions', 86400, function() {
    return Barikoi::administrative()->getDivisions();
});

// Cache districts for 24 hours
$districts = Cache::remember('barikoi_districts', 86400, function() {
    return Barikoi::administrative()->getDistricts();
});

// Cache ward geometry for 7 days
$wardGeometry = Cache::remember('barikoi_ward_geometry', 604800, function() {
    return Barikoi::administrative()->getAllWardGeometry();
});
```

## Error Handling Best Practices

```php
use Vendor\PackageName\Exceptions\BarikoiApiException;
use Vendor\PackageName\Exceptions\BarikoiValidationException;
use Illuminate\Support\Facades\Log;

try {
    $districts = Barikoi::administrative()->getDistricts('Dhaka');

} catch (BarikoiValidationException $e) {
    // Invalid input
    Log::warning('Invalid administrative query', [
        'error' => $e->getMessage(),
    ]);
    return response()->json(['error' => 'Invalid parameters'], 400);

} catch (BarikoiApiException $e) {
    // API error
    switch ($e->getCode()) {
        case 404:
            return response()->json(['error' => 'Not found'], 404);
        case 500:
            return response()->json(['error' => 'Service unavailable'], 503);
        default:
            throw $e;
    }
}
```

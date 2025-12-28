# Routing API Documentation

Complete documentation for all routing and navigation services.

---

## Transportation Profiles

All routing methods support these profiles:

| Profile | Description | Use Case |
|---------|-------------|----------|
| `car` | Driving routes (default) | Car navigation, delivery |
| `foot` | Walking routes | Pedestrian navigation |
| `motorcycle` | Motorcycle routes | Bike delivery, riders |
| `bike` | Bicycle routes | Cycling navigation |

---

## Route Overview

Get a simple route between multiple points.

### Method

```php
Barikoi::routeOverview(array $points, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `points` | array | Yes | Array of coordinate objects (min 2) |
| `options` | array | No | Routing options |

### Point Format

```php
[
    'longitude' => float,
    'latitude' => float
]
```

### Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `profile` | string | 'car' | Transportation mode. **Allowed values:** `car` or `foot` only |
| `geometries` | string | 'polyline' | Returned route geometry format (influences overview and per step). **Expected values:** `polyline`, `polyline6`, or `geojson` | 

### Usage

```php
use Vendor\PackageName\Facades\Barikoi;
use Vendor\PackageName\Exceptions\BarikoiValidationException;

try {
    $points = [
        ['longitude' => 90.3572, 'latitude' => 23.8067],
        ['longitude' => 90.3680, 'latitude' => 23.8100],
    ];

    // Car route (returns stdClass object)
    $route = Barikoi::routeOverview($points);

    // Walking route
    $walkingRoute = Barikoi::routeOverview($points, [
        'profile' => 'foot'
    ]);

    // Access response (object / stdClass)
    $thisRoute = $route->routes[0] ?? null;
    if ($thisRoute) {
        $distance = $thisRoute['distance']; // in meters
        $duration = $thisRoute['duration']; // in seconds
    }

} catch (BarikoiValidationException $e) {
    echo "Invalid route: " . $e->getMessage();
}
```

### Response

```php
{
    "code": "Ok",
    "routes": [
        {
            "distance": 1234,        // meters
            "duration": 456,         // seconds
            "geometry": "..."        // encoded polyline (polyline6 when requested)
        }
        // ... possibly more routes
    ]
}
```

### Conditions

- Minimum 2 points required
- Maximum 25 waypoints
- Points must be valid coordinates
- Points should be in Bangladesh

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Less than 2 points | Provide origin and destination |
| 400 | `BarikoiValidationException` | Invalid profile | Use only: `car` or `foot` |
| 400 | `BarikoiValidationException` | Invalid coordinates | Check lat/lng values |
| 404 | `BarikoiApiException` | No route found | Points may be too far or unreachable |

---

## Calculate Route

Calculate detailed route with navigation instructions using the routing API (returns object with `trip`).

### Method

```php
Barikoi::calculateRoute(array $startDestination, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `startDestination` | array | Yes | Array containing `start` and `destination` keys, each with `longitude` and `latitude` (see format below) |
| `options` | array | No | Routing options (`type`, `profile`, `country_code`) |

### Start/Destination Format

The `startDestination` parameter must be an array with the following structure:

```php
[
    'start' => [
        'longitude' => float,  // Start point longitude (-180 to 180)
        'latitude' => float     // Start point latitude (-90 to 90)
    ],
    'destination' => [
        'longitude' => float,  // Destination longitude (-180 to 180)
        'latitude' => float    // Destination latitude (-90 to 90)
    ]
]
```

**Validation:**
- Both `start` and `destination` keys are required
- Each must contain `longitude` and `latitude` keys
- Coordinates must be numeric
- Latitude must be between -90 and 90
- Longitude must be between -180 and 180

### Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `type` | string | `'vh'` | Routing engine type: `vh` (motorcycle only) or `gh` (all profiles) |
| `profile` | string | `'motorcycle'` | Transport profile: `motorcycle`, `car`, or `bike` |
| `country_code` | string | `'bgd'` | ISO Alpha-3 country code |

### Usage

```php
use Vendor\BarikoiApi\Facades\Barikoi;

// Motorcycle route with 'vh' type
$route = Barikoi::calculateRoute([
    'start' => [
        'longitude' => 90.36558776260725,
        'latitude' => 23.791645065364126
    ],
    'destination' => [
        'longitude' => 90.3676300089066,
        'latitude' => 23.784715477921843
    ],
], [
    'type' => 'vh',
    'profile' => 'motorcycle'
]);

// Car route with 'gh' type
$carRoute = Barikoi::calculateRoute([
    'start' => ['longitude' => 90.365588, 'latitude' => 23.791645],
    'destination' => ['longitude' => 90.367630, 'latitude' => 23.784715],
], [
    'type' => 'gh',
    'profile' => 'car'
]);
```

### Response

Returns navigation route with trip:
```php
{
    "trip": {
        "locations": [
            { "type": "break", "lat": 23.791645, "lon": 90.365587 },
            { "type": "break", "lat": 23.784715, "lon": 90.36763 }
        ],
        "legs": [
            {
                "maneuvers": [
                    {
                        "instruction": "Drive north.",
                        "time": 1.011,
                        "length": 0.006
                    }
                ],
                "summary": {
                    "length": 2.34,
                    "time": 320
                }
            }
        ]
    }
}
```

### Validation

The start/destination format includes automatic validation:
- Validates that `start` and `destination` keys exist
- Validates that both contain `longitude` and `latitude` keys
- Validates that coordinates are numeric
- Validates coordinate ranges (lat: -90 to 90, lng: -180 to 180)

### Error Handling

| Error | Exception | Cause | Solution |
|-------|-----------|-------|----------|
| Invalid format | `BarikoiValidationException` | Missing `start` or `destination` keys | Provide both keys |
| Invalid coordinates | `BarikoiValidationException` | Missing or invalid lat/lng | Check coordinate format |
| Invalid range | `BarikoiValidationException` | Coordinates out of range | Use valid lat (-90 to 90) and lng (-180 to 180) |
| Invalid type/profile | Object with `status: 400` | Unsupported combination | Use valid `type`/`profile` combination |

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
Barikoi::route()->overview(array $points, array $options = [])
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
| `profile` | string | 'car' | Transportation mode |

### Usage

```php
use Vendor\PackageName\Facades\Barikoi;
use Vendor\PackageName\Exceptions\BarikoiValidationException;

try {
    $points = [
        ['longitude' => 90.3572, 'latitude' => 23.8067],
        ['longitude' => 90.3680, 'latitude' => 23.8100],
    ];

    // Car route
    $route = Barikoi::route()->overview($points);

    // Walking route
    $walkingRoute = Barikoi::route()->overview($points, [
        'profile' => 'foot'
    ]);

    $distance = $route['distance']; // in meters
    $duration = $route['duration']; // in seconds

} catch (BarikoiValidationException $e) {
    echo "Invalid route: " . $e->getMessage();
}
```

### Response

```php
[
    'distance' => 1234,        // meters
    'duration' => 456,         // seconds
    'geometry' => '...',       // encoded polyline
    'status' => 200
]
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
| 400 | `BarikoiValidationException` | Invalid profile | Use: car, foot, motorcycle, bike |
| 400 | `BarikoiValidationException` | Invalid coordinates | Check lat/lng values |
| 404 | `BarikoiApiException` | No route found | Points may be too far or unreachable |

---

## Detailed Route

Get route with turn-by-turn directions.

### Method

```php
Barikoi::route()->detailed(array $points, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `points` | array | Yes | Array of coordinate objects |
| `options` | array | No | Routing options |

### Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `profile` | string | 'car' | Transportation mode |
| `steps` | boolean | false | Include turn-by-turn steps |
| `alternatives` | boolean | false | Include alternative routes |

### Usage

```php
try {
    $points = [
        ['longitude' => 90.3572, 'latitude' => 23.8067],
        ['longitude' => 90.3680, 'latitude' => 23.8100],
    ];

    $route = Barikoi::route()->detailed($points, [
        'profile' => 'car',
        'steps' => true,
        'alternatives' => true,
    ]);

    // Main route
    $mainRoute = $route['routes'][0];

    // Turn-by-turn instructions
    foreach ($mainRoute['legs'][0]['steps'] as $step) {
        echo $step['instruction'];
        echo "Distance: " . $step['distance'] . "m";
    }

    // Alternative routes
    if (isset($route['routes'][1])) {
        $alternativeRoute = $route['routes'][1];
    }

} catch (BarikoiValidationException $e) {
    echo "Route error: " . $e->getMessage();
}
```

### Response

```php
[
    'routes' => [
        [
            'distance' => 1234,
            'duration' => 456,
            'geometry' => '...',
            'legs' => [
                [
                    'steps' => [
                        [
                            'instruction' => 'Head north on Road ABC',
                            'distance' => 100,
                            'duration' => 20,
                        ],
                        // ... more steps
                    ]
                ]
            ]
        ],
        // ... alternative routes if requested
    ],
    'status' => 200
]
```

### Conditions

- Minimum 2 points
- Steps only available with `steps: true`
- Alternatives may not always be available

### Error Handling

Same as Route Overview

---

## Distance Calculation

Quick distance calculation between two points.

### Method

```php
Barikoi::route()->distance(
    float $fromLongitude,
    float $fromLatitude,
    float $toLongitude,
    float $toLatitude,
    array $options = []
)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `fromLongitude` | float | Yes | Origin longitude |
| `fromLatitude` | float | Yes | Origin latitude |
| `toLongitude` | float | Yes | Destination longitude |
| `toLatitude` | float | Yes | Destination latitude |
| `options` | array | No | Routing options |

### Usage

```php
try {
    $result = Barikoi::route()->distance(
        90.3572, 23.8067,  // From
        90.3680, 23.8100   // To
    );

    $distanceKm = round($result['routes'][0]['distance'] / 1000, 2);
    $durationMin = round($result['routes'][0]['duration'] / 60, 1);

    echo "Distance: {$distanceKm} km";
    echo "Duration: {$durationMin} minutes";

    // Walking distance
    $walking = Barikoi::route()->distance(
        90.3572, 23.8067,
        90.3680, 23.8100,
        ['profile' => 'foot']
    );

} catch (BarikoiValidationException $e) {
    echo "Calculation failed: " . $e->getMessage();
}
```

### Conditions

- All coordinates must be valid
- Returns driving distance by default
- Distance in meters, duration in seconds

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid coordinates | Verify all lat/lng values |
| 404 | `BarikoiApiException` | No route possible | Points unreachable by selected profile |

---

## Turn-by-Turn Directions

Get step-by-step navigation instructions.

### Method

```php
Barikoi::route()->directions(
    float $fromLongitude,
    float $fromLatitude,
    float $toLongitude,
    float $toLatitude,
    array $options = []
)
```

### Parameters

Same as Distance Calculation

### Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `profile` | string | 'car' | Transportation mode |
| `alternatives` | string | 'false' | Get alternative routes |

### Usage

```php
try {
    $directions = Barikoi::route()->directions(
        90.3572, 23.8067,
        90.3680, 23.8100,
        ['profile' => 'car', 'alternatives' => 'true']
    );

    foreach ($directions['routes'][0]['legs'][0]['steps'] as $step) {
        echo "➡️ " . $step['instruction'];
        echo " (" . $step['distance'] . "m, " . $step['duration'] . "s)";
    }

} catch (BarikoiApiException $e) {
    echo "Directions unavailable: " . $e->getMessage();
}
```

### Conditions

- Returns step-by-step instructions
- Instructions in English
- Includes street names when available

---

## Route Optimization

Optimize the order of multiple waypoints.

### Method

```php
Barikoi::route()->optimize(array $points, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `points` | array | Yes | Array of coordinate objects (min 3) |
| `options` | array | No | Optimization options |

### Usage

```php
try {
    $points = [
        ['longitude' => 90.3572, 'latitude' => 23.8067], // Start
        ['longitude' => 90.3680, 'latitude' => 23.8100], // Waypoint 1
        ['longitude' => 90.3750, 'latitude' => 23.8150], // Waypoint 2
        ['longitude' => 90.3820, 'latitude' => 23.8200], // End
    ];

    $optimized = Barikoi::route()->optimize($points, [
        'profile' => 'car'
    ]);

    $optimalOrder = $optimized['waypoint_order'];
    $totalDistance = $optimized['distance'];

} catch (BarikoiValidationException $e) {
    echo "Optimization failed: " . $e->getMessage();
}
```

### Conditions

- Minimum 3 points (start + waypoints + end)
- Maximum 12 points recommended
- First and last points are fixed
- Only middle waypoints are optimized

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Less than 3 points | Add more waypoints |
| 500 | `BarikoiApiException` | Optimization failed | Reduce number of points |

---

## Advanced Route Optimization

Optimize route with up to 50 waypoints.

### Method

```php
Barikoi::optimizedRoute(
    string $source,
    string $destination,
    array $waypoints = [],
    array $options = []
)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `source` | string | Yes | Start point "lat,lng" |
| `destination` | string | Yes | End point "lat,lng" |
| `waypoints` | array | No | Array of waypoint objects |
| `options` | array | No | Route options |

### Waypoint Format

```php
[
    'id' => int,          // Unique ID (1-50)
    'point' => string     // "latitude,longitude"
]
```

### Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `profile` | string | 'car' | car, motorcycle, or bike |

### Usage

```php
try {
    $route = Barikoi::optimizedRoute(
        '23.746086,90.37368',      // Source
        '23.746214,90.371654',     // Destination
        [
            ['id' => 1, 'point' => '23.746086,90.37368'],
            ['id' => 2, 'point' => '23.74577,90.373389'],
            ['id' => 3, 'point' => '23.74442,90.372909'],
            ['id' => 4, 'point' => '23.743961,90.37214'],
        ],
        ['profile' => 'car']
    );

    $totalDistance = $route['distance'];
    $totalDuration = $route['duration'];
    $optimizedOrder = $route['waypoint_order'];

    // Motorcycle route
    $motorcycleRoute = Barikoi::optimizedRoute(
        '23.746086,90.37368',
        '23.746214,90.371654',
        $waypoints,
        ['profile' => 'motorcycle']
    );

} catch (BarikoiValidationException $e) {
    echo "Route optimization failed: " . $e->getMessage();
}
```

### Conditions

- Maximum 50 waypoints
- Each waypoint must have unique ID
- IDs are sorted in ascending order
- Source and destination are fixed
- Valid profiles: car, motorcycle, bike

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid profile | Use car, motorcycle, or bike |
| 400 | `BarikoiValidationException` | Invalid point format | Use "lat,lng" format |
| 400 | `BarikoiValidationException` | Duplicate IDs | Ensure unique IDs |
| 400 | `BarikoiValidationException` | Too many waypoints (> 50) | Reduce waypoints |

---

## Detailed Navigation

Advanced navigation with step-by-step instructions.

### Method

```php
Barikoi::detailedNavigation(
    float $startLatitude,
    float $startLongitude,
    float $endLatitude,
    float $endLongitude,
    array $options = []
)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `startLatitude` | float | Yes | Origin latitude |
| `startLongitude` | float | Yes | Origin longitude |
| `endLatitude` | float | Yes | Destination latitude |
| `endLongitude` | float | Yes | Destination longitude |
| `options` | array | No | Navigation options |

### Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `profile` | string | 'car' | car, motorcycle, or bike |
| `country_code` | string | 'bgd' | ISO country code |

### Supported Countries

| Code | Country |
|------|---------|
| `bgd` | Bangladesh (default) |
| `ind` | India |
| `sau` | Saudi Arabia |
| `npl` | Nepal |

### Usage

```php
try {
    // Car navigation in Bangladesh
    $navigation = Barikoi::detailedNavigation(
        23.791645, 90.365588,    // Start
        23.784715, 90.367630     // End
    );

    // Motorcycle navigation
    $motorcycleNav = Barikoi::detailedNavigation(
        23.791645, 90.365588,
        23.784715, 90.367630,
        ['profile' => 'motorcycle']
    );

    // Bicycle navigation
    $bikeNav = Barikoi::detailedNavigation(
        23.791645, 90.365588,
        23.784715, 90.367630,
        ['profile' => 'bike']
    );

    // Navigation in Saudi Arabia
    $saudiNav = Barikoi::detailedNavigation(
        24.7136, 46.6753,
        24.7246, 46.6890,
        [
            'profile' => 'car',
            'country_code' => 'sau'
        ]
    );

    // Access route details
    $steps = $navigation['routes'][0]['legs'][0]['steps'];
    foreach ($steps as $step) {
        echo $step['instruction'];
    }

} catch (BarikoiValidationException $e) {
    echo "Navigation error: " . $e->getMessage();
}
```

### Response

```php
[
    'routes' => [
        [
            'distance' => 1234,
            'duration' => 456,
            'legs' => [
                [
                    'steps' => [
                        [
                            'instruction' => 'Turn right onto Main Street',
                            'distance' => 100,
                            'duration' => 25,
                            'maneuver' => [
                                'type' => 'turn',
                                'modifier' => 'right'
                            ]
                        ],
                        // ... more steps
                    ]
                ]
            ]
        ]
    ]
]
```

### Conditions

- Coordinates must be in specified country
- Profile must be: car, motorcycle, or bike
- Returns detailed turn-by-turn instructions
- Includes street names and landmarks

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid profile | Use car, motorcycle, or bike |
| 400 | `BarikoiValidationException` | Invalid country code | Use valid ISO code |
| 404 | `BarikoiApiException` | No route found | Check if points are in specified country |

---

## Route Match

Match GPS trace to road network.

### Method

```php
Barikoi::route()->match(array $gpsPoints, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `gpsPoints` | array | Yes | Array of GPS coordinate objects |
| `options` | array | No | Matching options |

### GPS Point Format

```php
[
    'longitude' => float,
    'latitude' => float
]
```

### Usage

```php
try {
    $gpsTrace = [
        ['longitude' => 90.3572, 'latitude' => 23.8067],
        ['longitude' => 90.3575, 'latitude' => 23.8068],
        ['longitude' => 90.3578, 'latitude' => 23.8069],
        ['longitude' => 90.3582, 'latitude' => 23.8071],
    ];

    $matched = Barikoi::route()->match($gpsTrace, [
        'profile' => 'car'
    ]);

    $matchedPoints = $matched['matched_points'];
    $confidence = $matched['confidence'];

} catch (BarikoiValidationException $e) {
    echo "Matching failed: " . $e->getMessage();
}
```

### Conditions

- Minimum 2 GPS points
- Maximum 100 points per request
- Points should be chronological
- Points should be on roads
- Works best with points 5-10 seconds apart

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Less than 2 points | Provide more GPS points |
| 400 | `BarikoiValidationException` | Too many points (> 100) | Split into batches |
| 400 | `BarikoiValidationException` | Invalid coordinates | Check all lat/lng values |

---

## General Error Handling

All routing methods may throw these errors:

### Common Errors

```php
use Vendor\PackageName\Exceptions\BarikoiApiException;
use Vendor\PackageName\Exceptions\BarikoiValidationException;

try {
    $route = Barikoi::route()->detailed($points);
} catch (BarikoiValidationException $e) {
    // Handle validation errors (400)
    // - Invalid coordinates
    // - Invalid profile
    // - Invalid parameters
    Log::warning('Invalid route parameters', [
        'error' => $e->getMessage(),
        'params' => $points
    ]);
} catch (BarikoiApiException $e) {
    // Handle API errors (401, 404, 429, 500)
    switch ($e->getCode()) {
        case 401:
            // Invalid API key
            break;
        case 404:
            // No route found
            break;
        case 429:
            // Rate limit exceeded
            break;
        case 500:
            // Server error
            break;
    }
}
```

### Best Practices

1. **Always validate coordinates before sending**
   ```php
   if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
       throw new \InvalidArgumentException('Invalid coordinates');
   }
   ```

2. **Handle 404 gracefully**
   ```php
   } catch (BarikoiApiException $e) {
       if ($e->getCode() === 404) {
           return response()->json(['error' => 'No route found'], 200);
       }
   }
   ```

3. **Implement retry for 500 errors**
   ```php
   } catch (BarikoiApiException $e) {
       if ($e->getCode() === 500) {
           // Retry after 2 seconds
           sleep(2);
           return $this->calculateRoute($points);
       }
   }
   ```

4. **Cache routes to avoid rate limits**
   ```php
   $cacheKey = "route_" . md5(json_encode($points));
   return Cache::remember($cacheKey, 3600, function() use ($points) {
       return Barikoi::route()->detailed($points);
   });
   ```

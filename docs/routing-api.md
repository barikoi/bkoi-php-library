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
    $route = Barikoi::routeOverview($points);

    // Walking route
    $walkingRoute = Barikoi::routeOverview($points, [
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
Barikoi::calculateRoute(array $points, array $options = [])
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

    $route = Barikoi::calculateRoute($points, [
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
       return Barikoi::calculateRoute($points);
   });
   ```

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
| 400 | `BarikoiValidationException` | Invalid profile | Use: car, foot, motorcycle, bike |
| 400 | `BarikoiValidationException` | Invalid coordinates | Check lat/lng values |
| 404 | `BarikoiApiException` | No route found | Points may be too far or unreachable |

---

## Detailed Route (Navigation API)

Get detailed navigation route with turn-by-turn maneuvers.

### Method

```php
Barikoi::detailedNavigation(float $startLat, float $startLng, float $destLat, float $destLng, array $options = [])
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `startLat` | float | Yes | Start latitude |
| `startLng` | float | Yes | Start longitude |
| `destLat` | float | Yes | Destination latitude |
| `destLng` | float | Yes | Destination longitude |
| `options` | array | No | Routing options (`type`, `profile`, `country_code`) |

### Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `type` | string | `'vh'` | Routing engine type (`vh` or `gh`) |
| `profile` | string | `'motorcycle'` | Transport profile: `motorcycle`, `car`, or `bike` |
| `country_code` | string | `'bgd'` | ISO Alpha-3 country code |

### Usage

```php
use Vendor\PackageName\Facades\Barikoi;

// Basic navigation (returns stdClass with \"trip\")
$result = Barikoi::detailedNavigation(
    23.791645065364126, 90.36558776260725,
    23.784715477921843, 90.3676300089066,
    ['type' => 'vh'] // motorcycle default
);

// Access trip summary
$trip = $result->trip ?? null;
if ($trip) {
    $legs = $trip['legs'] ?? [];
    $firstLeg = $legs[0] ?? null;
    if ($firstLeg) {
        $maneuvers = $firstLeg['maneuvers'] ?? [];
        foreach ($maneuvers as $maneuver) {
            echo $maneuver['instruction'] . PHP_EOL;
        }
    }
}
```

### Response

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
                        "length": 0.006,
                        "cost": 1.213
                    }
                    // ... more maneuvers
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

### Conditions

- Start and destination must be valid coordinates
- Supported profiles depend on `type` (`vh` vs `gh`)

### Error Handling

- Returns an object with `status`, `error`, and `message` for invalid `type`/`profile` combinations.

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

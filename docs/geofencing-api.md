# Geofencing API Documentation

Complete documentation for geofence management and location checking.

---

## Create Geofence Point

Create a new geofence with a center point and radius.

### Method

```php
Barikoi::geofence()->setPoint(
    string $name,
    float $longitude,
    float $latitude,
    int $radius
)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `name` | string | Yes | Geofence name/label |
| `longitude` | float | Yes | Center point longitude |
| `latitude` | float | Yes | Center point latitude |
| `radius` | int | Yes | Radius in meters |

### Usage

```php
use Vendor\PackageName\Facades\Barikoi;
use Vendor\PackageName\Exceptions\BarikoiValidationException;
use Vendor\PackageName\Exceptions\BarikoiApiException;

try {
    $geofence = Barikoi::geofence()->setPoint(
        'Office Location',    // name
        90.3572,             // longitude
        23.8067,             // latitude
        100                  // radius in meters
    );

    $geofenceId = $geofence['id'];
    echo "Geofence created with ID: {$geofenceId}";

} catch (BarikoiValidationException $e) {
    echo "Invalid parameters: " . $e->getMessage();
} catch (BarikoiApiException $e) {
    echo "API Error: " . $e->getMessage();
}
```

### Response

```php
[
    'id' => 'geofence_123abc',
    'name' => 'Office Location',
    'longitude' => 90.3572,
    'latitude' => 23.8067,
    'radius' => 100,
    'created_at' => '2024-01-15 10:30:00',
    'status' => 201
]
```

### Conditions

- Name: 1-255 characters
- Longitude: -180 to 180
- Latitude: -90 to 90
- Radius: 1 to 100,000 meters (100km)
- Maximum 1000 geofences per account

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Empty name | Provide geofence name |
| 400 | `BarikoiValidationException` | Invalid coordinates | Check lat/lng values |
| 400 | `BarikoiValidationException` | Invalid radius | Use 1-100,000 meters |
| 401 | `BarikoiApiException` | Invalid API key | Check credentials |
| 403 | `BarikoiApiException` | Quota exceeded | Delete unused geofences |

---

## Get All Geofence Points

Retrieve all your geofences.

### Method

```php
Barikoi::geofence()->getPoints()
```

### Parameters

None

### Usage

```php
try {
    $geofences = Barikoi::geofence()->getPoints();

    foreach ($geofences['geofences'] as $geofence) {
        echo "Name: {$geofence['name']}";
        echo "Radius: {$geofence['radius']}m";
        echo "Location: ({$geofence['latitude']}, {$geofence['longitude']})";
    }

} catch (BarikoiApiException $e) {
    echo "Error: " . $e->getMessage();
}
```

### Response

```php
[
    'geofences' => [
        [
            'id' => 'geofence_123',
            'name' => 'Office',
            'longitude' => 90.3572,
            'latitude' => 23.8067,
            'radius' => 100,
            'created_at' => '2024-01-15 10:30:00'
        ],
        // ... more geofences
    ],
    'total' => 15,
    'status' => 200
]
```

### Conditions

- Returns all geofences for your API key
- Ordered by creation date (newest first)
- Maximum 1000 geofences returned

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 401 | `BarikoiApiException` | Invalid API key | Check credentials |
| 500 | `BarikoiApiException` | Server error | Retry after delay |

---

## Get Specific Geofence Point

Retrieve details of a specific geofence.

### Method

```php
Barikoi::geofence()->getPoint(string $geofenceId)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `geofenceId` | string | Yes | Geofence identifier |

### Usage

```php
try {
    $geofence = Barikoi::geofence()->getPoint('geofence_123abc');

    echo "Name: {$geofence['name']}";
    echo "Center: ({$geofence['latitude']}, {$geofence['longitude']})";
    echo "Radius: {$geofence['radius']}m";

} catch (BarikoiApiException $e) {
    if ($e->getCode() === 404) {
        echo "Geofence not found";
    }
}
```

### Response

```php
[
    'id' => 'geofence_123abc',
    'name' => 'Office Location',
    'longitude' => 90.3572,
    'latitude' => 23.8067,
    'radius' => 100,
    'created_at' => '2024-01-15 10:30:00',
    'updated_at' => '2024-01-16 14:20:00',
    'status' => 200
]
```

### Conditions

- Geofence must belong to your API key
- ID must be exact match

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Empty geofence ID | Provide valid ID |
| 404 | `BarikoiApiException` | Geofence not found | Check ID exists |
| 403 | `BarikoiApiException` | Not your geofence | Verify ownership |

---

## Update Geofence Point

Update radius and/or name of existing geofence.

### Method

```php
Barikoi::geofence()->updatePoint(
    string $geofenceId,
    int $radius = null,
    string $name = null
)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `geofenceId` | string | Yes | Geofence identifier |
| `radius` | int | No | New radius in meters |
| `name` | string | No | New name |

### Usage

```php
try {
    // Update radius only
    $updated = Barikoi::geofence()->updatePoint('geofence_123', 150);

    // Update name only
    $updated = Barikoi::geofence()->updatePoint('geofence_123', null, 'New Office');

    // Update both
    $updated = Barikoi::geofence()->updatePoint('geofence_123', 200, 'Main Office');

    echo "Geofence updated successfully";

} catch (BarikoiValidationException $e) {
    echo "Invalid update: " . $e->getMessage();
} catch (BarikoiApiException $e) {
    if ($e->getCode() === 404) {
        echo "Geofence not found";
    }
}
```

### Response

```php
[
    'id' => 'geofence_123',
    'name' => 'New Office',
    'longitude' => 90.3572,
    'latitude' => 23.8067,
    'radius' => 150,
    'updated_at' => '2024-01-16 15:45:00',
    'status' => 200
]
```

### Conditions

- At least one parameter (radius or name) must be provided
- Radius: 1-100,000 meters
- Name: 1-255 characters
- Cannot update coordinates (create new geofence instead)

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | No parameters provided | Provide radius or name |
| 400 | `BarikoiValidationException` | Invalid radius | Use 1-100,000 |
| 404 | `BarikoiApiException` | Geofence not found | Verify ID |
| 403 | `BarikoiApiException` | Not your geofence | Check ownership |

---

## Delete Geofence Point

Permanently delete a geofence.

### Method

```php
Barikoi::geofence()->deletePoint(string $geofenceId)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `geofenceId` | string | Yes | Geofence identifier |

### Usage

```php
try {
    $result = Barikoi::geofence()->deletePoint('geofence_123');

    if ($result['deleted']) {
        echo "Geofence deleted successfully";
    }

} catch (BarikoiApiException $e) {
    if ($e->getCode() === 404) {
        echo "Geofence already deleted or doesn't exist";
    }
}
```

### Response

```php
[
    'id' => 'geofence_123',
    'deleted' => true,
    'deleted_at' => '2024-01-16 16:00:00',
    'status' => 200
]
```

### Conditions

- Deletion is permanent and cannot be undone
- Deleted geofences are removed immediately
- ID is freed for reuse

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Empty geofence ID | Provide valid ID |
| 404 | `BarikoiApiException` | Geofence not found | Already deleted or invalid ID |
| 403 | `BarikoiApiException` | Not your geofence | Verify ownership |

---

## Check Geofence

Check if coordinates are inside any of your geofences.

### Method

```php
Barikoi::geofence()->checkGeofence(float $longitude, float $latitude)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `longitude` | float | Yes | Point longitude to check |
| `latitude` | float | Yes | Point latitude to check |

### Usage

```php
try {
    $result = Barikoi::geofence()->checkGeofence(90.3572, 23.8067);

    if ($result['inside_geofence']) {
        echo "Inside geofence: {$result['geofence']['name']}";
        echo "Distance from center: {$result['distance_from_center']}m";
    } else {
        echo "Not inside any geofence";
    }

} catch (BarikoiValidationException $e) {
    echo "Invalid coordinates: " . $e->getMessage();
}
```

### Response (Inside Geofence)

```php
[
    'inside_geofence' => true,
    'geofence' => [
        'id' => 'geofence_123',
        'name' => 'Office Location',
        'radius' => 100,
        'center' => [
            'longitude' => 90.3570,
            'latitude' => 23.8065
        ]
    ],
    'distance_from_center' => 35.5,  // meters
    'status' => 200
]
```

### Response (Outside All Geofences)

```php
[
    'inside_geofence' => false,
    'nearest_geofence' => [
        'id' => 'geofence_456',
        'name' => 'Warehouse',
        'distance' => 523.2  // meters to nearest geofence
    ],
    'status' => 200
]
```

### Conditions

- Checks against ALL your geofences
- Returns first matching geofence if multiple overlap
- Calculates distance from geofence center
- Coordinates must be valid

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid coordinates | Check lat/lng values |
| 401 | `BarikoiApiException` | Invalid API key | Check credentials |

---

## Check Nearby

Check if current location is within radius of a destination.

### Method

```php
Barikoi::geofence()->checkNearby(
    float $destinationLatitude,
    float $destinationLongitude,
    int $radius,
    float $currentLatitude,
    float $currentLongitude
)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `destinationLatitude` | float | Yes | Target location latitude |
| `destinationLongitude` | float | Yes | Target location longitude |
| `radius` | int | Yes | Acceptable radius (meters) |
| `currentLatitude` | float | Yes | Current position latitude |
| `currentLongitude` | float | Yes | Current position longitude |

### Usage

```php
try {
    $result = Barikoi::geofence()->checkNearby(
        23.8067,  // destination latitude
        90.3572,  // destination longitude
        100,      // radius in meters
        23.8070,  // current latitude
        90.3575   // current longitude
    );

    if ($result['isWithin']) {
        echo "Within {$radius}m of destination";
        echo "Actual distance: {$result['distance']}m";
    } else {
        echo "Outside range";
        echo "Distance: {$result['distance']}m";
    }

} catch (BarikoiValidationException $e) {
    echo "Invalid parameters: " . $e->getMessage();
}
```

### Response (Within Range)

```php
[
    'isWithin' => true,
    'distance' => 45.7,          // actual distance in meters
    'radius' => 100,             // specified radius
    'destination' => [
        'latitude' => 23.8067,
        'longitude' => 90.3572
    ],
    'current' => [
        'latitude' => 23.8070,
        'longitude' => 90.3575
    ],
    'status' => 200
]
```

### Response (Outside Range)

```php
[
    'isWithin' => false,
    'distance' => 156.3,
    'radius' => 100,
    'how_far_outside' => 56.3,   // meters beyond radius
    'status' => 200
]
```

### Conditions

- Radius: 1 to 100,000 meters
- All coordinates must be valid
- Calculates straight-line distance (haversine formula)
- Does not consider roads/routing

### Error Handling

| Error Code | Exception | Cause | Solution |
|------------|-----------|-------|----------|
| 400 | `BarikoiValidationException` | Invalid coordinates | Validate all lat/lng |
| 400 | `BarikoiValidationException` | Invalid radius | Use 1-100,000 meters |

---

## Use Cases and Examples

### Use Case 1: Delivery Zone Check

```php
public function checkDeliveryZone(Request $request)
{
    try {
        // Check if customer is in delivery zone
        $result = Barikoi::geofence()->checkGeofence(
            $request->longitude,
            $request->latitude
        );

        if ($result['inside_geofence']) {
            $zone = $result['geofence']['name'];
            return response()->json([
                'can_deliver' => true,
                'zone' => $zone,
                'delivery_fee' => $this->calculateFee($zone)
            ]);
        }

        return response()->json([
            'can_deliver' => false,
            'message' => 'Outside delivery area',
            'nearest_zone' => $result['nearest_geofence']['name'] ?? null
        ]);

    } catch (BarikoiValidationException $e) {
        return response()->json(['error' => 'Invalid location'], 400);
    }
}
```

### Use Case 2: Employee Attendance

```php
public function checkInEmployee(Request $request)
{
    try {
        $officeLocation = [23.8067, 90.3572];
        $attendanceRadius = 50; // 50 meters

        $result = Barikoi::geofence()->checkNearby(
            $officeLocation[0],
            $officeLocation[1],
            $attendanceRadius,
            $request->latitude,
            $request->longitude
        );

        if ($result['isWithin']) {
            // Mark attendance
            $this->markAttendance($request->user_id);

            return response()->json([
                'checked_in' => true,
                'distance_from_office' => $result['distance'] . 'm'
            ]);
        }

        return response()->json([
            'checked_in' => false,
            'error' => 'You must be within ' . $attendanceRadius . 'm of office',
            'your_distance' => $result['distance'] . 'm'
        ], 403);

    } catch (BarikoiApiException $e) {
        return response()->json(['error' => 'Service unavailable'], 503);
    }
}
```

### Use Case 3: Location-Based Notifications

```php
public function checkUserLocation(User $user)
{
    try {
        $result = Barikoi::geofence()->checkGeofence(
            $user->longitude,
            $user->latitude
        );

        if ($result['inside_geofence']) {
            $geofence = $result['geofence'];

            // Send notification based on geofence
            switch ($geofence['name']) {
                case 'Store A':
                    $this->sendOffer($user, 'Welcome to Store A! 20% off today!');
                    break;
                case 'Airport':
                    $this->sendAlert($user, 'Flight reminder');
                    break;
            }
        }

    } catch (BarikoiApiException $e) {
        Log::error('Geofence check failed', ['error' => $e->getMessage()]);
    }
}
```

### Use Case 4: Fleet Management

```php
public function trackVehicle(Request $request)
{
    try {
        // Check if vehicle is in restricted area
        $result = Barikoi::geofence()->checkGeofence(
            $request->vehicle_longitude,
            $request->vehicle_latitude
        );

        if ($result['inside_geofence']) {
            $zone = $result['geofence'];

            if (str_contains($zone['name'], 'Restricted')) {
                // Alert: Vehicle in restricted area
                $this->sendAlert($request->vehicle_id, $zone['name']);
            }
        }

        // Log vehicle location
        $this->logVehiclePosition(
            $request->vehicle_id,
            $request->vehicle_latitude,
            $request->vehicle_longitude,
            $result
        );

    } catch (BarikoiApiException $e) {
        Log::error('Vehicle tracking failed', ['error' => $e->getMessage()]);
    }
}
```

---

## Best Practices

### 1. Cache Geofences

```php
use Illuminate\Support\Facades\Cache;

// Cache geofences for 1 hour
$geofences = Cache::remember('my_geofences', 3600, function() {
    return Barikoi::geofence()->getPoints();
});
```

### 2. Batch Location Checks

```php
// Instead of checking each user separately, batch them
$users = User::all();
$results = [];

foreach ($users as $user) {
    $results[$user->id] = Barikoi::geofence()->checkGeofence(
        $user->longitude,
        $user->latitude
    );

    // Rate limiting - wait between requests
    usleep(100000); // 100ms delay
}
```

### 3. Handle Errors Gracefully

```php
try {
    $result = Barikoi::geofence()->checkGeofence($lng, $lat);
} catch (BarikoiValidationException $e) {
    // Invalid input - return default
    return ['inside_geofence' => false];
} catch (BarikoiApiException $e) {
    // API error - use fallback logic
    if ($e->getCode() === 500) {
        return $this->getFallbackGeofenceCheck($lng, $lat);
    }
    throw $e;
}
```

### 4. Delete Unused Geofences

```php
// Clean up old geofences to stay within quota
$geofences = Barikoi::geofence()->getPoints();

foreach ($geofences['geofences'] as $geofence) {
    $createdAt = Carbon::parse($geofence['created_at']);

    if ($createdAt->diffInDays(now()) > 30) {
        // Delete geofences older than 30 days
        Barikoi::geofence()->deletePoint($geofence['id']);
    }
}
```

# Barikoi Laravel Package

A comprehensive Laravel package for integrating [Barikoi API](https://barikoi.com) - Bangladesh's leading location data provider.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE.md)

## Features

- 🗺️ **Location Services**: Geocoding, Reverse Geocoding, Autocomplete, Place Search
- 🛣️ **Routing**: Route calculation, optimization, and turn-by-turn navigation
- 🏛️ **Administrative Data**: Divisions, Districts, Thanas, Unions, Wards, Zones
- 📍 **Geofencing**: Create and manage geofences, check point locations
- 🔍 **Nearby Search**: Find places within a radius
- 🛤️ **Snap to Road**: GPS coordinate correction
- ⚠️ **Error Handling**: User-friendly exceptions with actionable error messages

---

## Installation

```bash
composer require barikoi/barikoi-api
```

## Configuration

1. Publish the configuration file:

```bash
php artisan vendor:publish --provider="Vendor\PackageName\PackageNameServiceProvider" --tag="config"
```

2. Add your Barikoi API credentials to `.env`:

```env
BARIKOI_API_KEY=your_api_key_here
BARIKOI_BASE_URL=https://barikoi.xyz/v2/api
```

Get your API key from [Barikoi](https://barikoi.com).

---

## Quick Start

```php
use Vendor\PackageName\Facades\Barikoi;

// Get address from coordinates
$address = Barikoi::reverseGeocode(90.3572, 23.8067);

// Convert address to coordinates
$coordinates = Barikoi::geocode('Dhanmondi, Dhaka');

// Search for places
$places = Barikoi::searchPlace('restaurant');

// Calculate route
$route = Barikoi::route()->distance(90.3572, 23.8067, 90.3680, 23.8100);

// Check geofence
$result = Barikoi::geofence()->checkGeofence(90.3572, 23.8067);
```

---

## Documentation

### 📚 API Documentation

Complete documentation with parameters, conditions, and error handling for each API:

| API Service | Documentation |
|-------------|---------------|
| **Location Services** | [docs/location-api.md](docs/location-api.md) |
| - Reverse Geocoding | Convert coordinates to address |
| - Geocoding (Rupantor) | Convert address to coordinates |
| - Autocomplete | Place suggestions |
| - Search Place | Find specific places |
| - Nearby Search | Find places within radius |
| - Snap to Road | Correct GPS coordinates |
| - Point in Polygon | Check point inside area |
| | |
| **Routing Services** | [docs/routing-api.md](docs/routing-api.md) |
| - Route Overview | Simple route calculation |
| - Detailed Route | Turn-by-turn directions |
| - Distance Calculation | Quick distance check |
| - Route Optimization | Optimize waypoint order |
| - Advanced Optimization | Up to 50 waypoints |
| - Detailed Navigation | Step-by-step navigation |
| - Route Match | Match GPS to roads |
| | |
| **Administrative Services** | [docs/administrative-api.md](docs/administrative-api.md) |
| - Divisions | Get all divisions |
| - Districts | Get districts |
| - Subdistricts | Get upazilas |
| - Thanas | Get police stations |
| - Unions | Get unions |
| - Wards & Zones | Get city corporation data |
| | |
| **Geofencing Services** | [docs/geofencing-api.md](docs/geofencing-api.md) |
| - Create Geofence | Set geofence point |
| - Get Geofences | List all geofences |
| - Update Geofence | Modify geofence |
| - Delete Geofence | Remove geofence |
| - Check Geofence | Check if point inside |
| - Check Nearby | Check distance from point |

---

## Usage

### Using Facade (Recommended)

```php
use Vendor\PackageName\Facades\Barikoi;

$address = Barikoi::reverseGeocode(90.3572, 23.8067);
$places = Barikoi::searchPlace('restaurant');
```

### Using Dependency Injection

```php
use Vendor\PackageName\Barikoi;

class LocationController extends Controller
{
    public function show(Barikoi $barikoi)
    {
        $address = $barikoi->location()->reverseGeocode(90.3572, 23.8067);
        return response()->json($address);
    }
}
```

---

## Error Handling

The package provides comprehensive error handling with user-friendly messages.

### Exception Types

**`BarikoiValidationException`** - Validation errors (400)
- Invalid coordinates
- Missing parameters
- Invalid input values

**`BarikoiApiException`** - API errors (401, 404, 429, 500, etc.)
- 401: Invalid API key
- 404: Resource not found
- 429: Rate limit exceeded
- 500: Server error

### Basic Usage

```php
use Vendor\PackageName\Facades\Barikoi;
use Vendor\PackageName\Exceptions\BarikoiApiException;
use Vendor\PackageName\Exceptions\BarikoiValidationException;

try {
    $result = Barikoi::reverseGeocode(90.3572, 23.8067);

} catch (BarikoiValidationException $e) {
    // Handle validation errors (400)
    echo "Invalid input: " . $e->getMessage();

} catch (BarikoiApiException $e) {
    // Handle API errors (401, 404, 500, etc.)
    echo "API Error: " . $e->getMessage();
}
```

### Error Messages

| Code | Message |
|------|---------|
| 400 | `Validation Error: Invalid coordinates. Please check your input parameters.` |
| 401 | `Authentication Failed: Invalid API key. Please verify your API key is correct.` |
| 404 | `Not Found: Resource not found. The requested resource or endpoint does not exist.` |
| 429 | `Rate Limit Exceeded: Too many requests. Please reduce the number of requests...` |
| 500 | `Server Error: Internal Server Error. The Barikoi API is experiencing issues...` |

### Getting Error Details

```php
catch (BarikoiValidationException $e) {
    $message = $e->getMessage();              // User-friendly message
    $apiMessage = $e->getErrorMessage();      // Original API message
    $statusCode = $e->getCode();              // HTTP status code
    $errorData = $e->getErrorData();          // Full error response
    $validationErrors = $e->getValidationErrors(); // Field-specific errors
}
```

See individual API documentation for specific error conditions and solutions.

---

## Examples

### Example 1: Get Address from GPS

```php
public function getAddress(Request $request)
{
    try {
        $result = Barikoi::reverseGeocode(
            $request->longitude,
            $request->latitude,
            ['district' => true, 'bangla' => true]
        );

        return response()->json([
            'address' => $result['place']['address'],
            'district' => $result['place']['district'],
        ]);
    } catch (BarikoiApiException $e) {
        return response()->json(['error' => $e->getMessage()], $e->getCode());
    }
}
```

### Example 2: Calculate Route

```php
public function calculateRoute(Request $request)
{
    try {
        $origin = ['longitude' => 90.3572, 'latitude' => 23.8067];
        $destination = ['longitude' => 90.3680, 'latitude' => 23.8100];

        $route = Barikoi::route()->detailed([$origin, $destination], [
            'steps' => true
        ]);

        return response()->json([
            'distance_km' => round($route['routes'][0]['distance'] / 1000, 2),
            'duration_min' => round($route['routes'][0]['duration'] / 60, 1),
            'steps' => $route['routes'][0]['legs'][0]['steps'],
        ]);
    } catch (BarikoiApiException $e) {
        return response()->json(['error' => 'Route calculation failed'], 500);
    }
}
```

### Example 3: Check Delivery Zone

```php
public function checkDeliveryZone(Request $request)
{
    try {
        $result = Barikoi::geofence()->checkGeofence(
            $request->longitude,
            $request->latitude
        );

        if ($result['inside_geofence']) {
            return response()->json(['can_deliver' => true]);
        }

        return response()->json([
            'can_deliver' => false,
            'message' => 'Outside delivery zone'
        ]);
    } catch (BarikoiApiException $e) {
        return response()->json(['error' => 'Service unavailable'], 503);
    }
}
```

---

## Testing

```bash
composer test
```

---

## API Reference

For detailed Barikoi API documentation, visit [Barikoi API Documentation](https://docs.barikoi.com/api).

---

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

---

## License

The MIT License (MIT). See [License File](LICENSE.md) for more information.

---

## Credits

- [Barikoi Technologies Limited](https://www.barikoi.com/)
- Package developed for easy Laravel integration

---

## Support

For issues or questions:
- Create an issue on GitHub
- Check the detailed API documentation in `docs/` folder
- Visit [Barikoi Support](https://barikoi.com/support)

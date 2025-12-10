# Changelog

All notable changes to this package will be documented in this file.

## [1.0.0] - 2025-11-25

### Added
- Initial release of Barikoi Laravel Package
- BarikoiClient for HTTP communication with Barikoi API
- LocationService with the following features:
  - Reverse Geocoding (coordinates to address)
  - Geocoding (address to coordinates) via Rupantor
  - Autocomplete for place suggestions
  - Place search functionality
  - Get place details by ID
  - Snap to Road for GPS correction
  - Nearby search (basic, with category, with multiple types)
  - Point in Polygon check
- RouteService with the following features:
  - Route overview
  - Detailed route calculation
  - Route optimization for multiple waypoints
  - Location-optimized routing
  - Route matching (GPS trace to road network)
- AdministrativeService with the following features:
  - Get Divisions, Districts, Subdistricts (Upazilas)
  - Get Thanas, Unions, Areas
  - Get City with Areas
  - Get Ward and Zone information by coordinates
  - Get Ward geometry (all and specific)
  - Get all Zones
  - Get City Corporation by geolocation
- GeofenceService with the following features:
  - Create geofence points
  - Get all geofence points
  - Get specific geofence point by ID
  - Update geofence points
  - Delete geofence points
  - Check if coordinates are inside geofence
  - Check nearby location within specified radius
- Barikoi Facade for easy access
- Comprehensive documentation and examples
- Configuration file for API credentials
- Service Provider with automatic Laravel integration

### API Endpoints Covered
Based on Barikoi API v2.0 Documentation (https://docs.barikoi.com/api):
- ✅ Reverse Geocoding
- ✅ Autocomplete
- ✅ Rupantor Geocoder (Geocoding)
- ✅ Route Overview
- ✅ Calculate Detailed Route
- ✅ Route Optimization
- ✅ Route Location Optimized
- ✅ Route Match
- ✅ Search Place
- ✅ Get Place Details
- ✅ Snap to Road
- ✅ Divisions
- ✅ Districts
- ✅ Subdistricts
- ✅ Thanas
- ✅ City with Areas
- ✅ Union
- ✅ Area
- ✅ Ward & Zone from LatLng
- ✅ Ward from LatLng
- ✅ All Ward Geometry
- ✅ Specific Ward Geometry
- ✅ All Zones
- ✅ Zone from LatLng
- ✅ Nearby API
- ✅ Nearby API with Category
- ✅ Nearby API with Multiple Types
- ✅ Point in Polygon
- ✅ Set Geofence Point
- ✅ Get Geofence Points
- ✅ Get Geofence Point by ID
- ✅ Update Geofence Point by ID
- ✅ Delete Geofence Point by ID
- ✅ Check Geofence
- ✅ Check Nearby Location
- ✅ City Corporation by Geolocation

### Requirements
- PHP ^8.1|^8.2|^8.3
- Laravel ^10.0|^11.0
- Barikoi API Key (get from https://barikoi.com)


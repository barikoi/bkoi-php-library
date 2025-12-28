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
  - Nearby search
  - Point in Polygon check
- RouteService with the following features:
  - Route overview
  - Detailed route calculation
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
- ✅ Search Place
- ✅ Get Place Details
- ✅ Snap to Road
- ✅ Nearby API
- ✅ Check Nearby Location

### Requirements
- PHP ^8.1|^8.2|^8.3
- Laravel ^10.0|^11.0
- Barikoi API Key (get from https://developer.barikoi.com)


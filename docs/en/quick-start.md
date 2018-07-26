# Quick Start

## Add Address fields

1. Install via composer.

2. Apply the "Addressable" extension to your SiteTree or DataObject class to automatically add address fields.
```yml
Page:
  extensions:
    - Symbiote\Addressable\Addressable
```

## Transform Address field data into a latitude and longitude

1. First, complete the steps above to "Add Address fields"

2. Apply the "Geocodable" extension to your SiteTree or DataObject class. You will also need the Addressable extension applied as well.
```yml
Page:
  extensions:
    - Symbiote\Addressable\Addressable
    - Symbiote\Addressable\Geocodable
```

3. It is highly recommended that you configure a Google API key or else you will most likely hit "over your quota" issues very quickly.
```yml
Symbiote\Addressable\GeocodeService:
  google_api_key: 'API_KEY_HERE'
```

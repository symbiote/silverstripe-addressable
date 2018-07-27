# Advanced Configuration


## Configure Geocodable Service

```yml
Symbiote\Addressable\GeocodeService:
  google_api_url: 'https://maps.googleapis.com/maps/api/geocode/xml' # This is already defined as the default value.
  google_api_key: 'API_KEY_HERE' # Recommended! You will hit quota limit issues in production without this!
```

## Change regex to validate postcode

```yml
Symbiote\Addressable\Addressable:
  postcode_regex: '/^[0-9A-Za-z]+$/'
```

## Lock to 1 country or state

You can lock down Addressable to only use 1 country or 1 state by configuring it as shown below.
When you only have 1 country or 1 state, the `Country` or `State` field will be automatically populated when a new record is created. (Before it's even written)

### Global setting (affects all DataObjects using Addressable)
```yml
Symbiote\Addressable\Addressable:
  allowed_countries:
    au: 'Australia'
  allowed_states:
    vic: 'Victoria'
```

### Local setting (affects the targetted DataObjects)
You can also change what countries and states are available on a per-DataObject level like so:
```yml
Page:
  extensions:
    - Symbiote\Addressable\Addressable
  allowed_countries:
    au: 'Australia'
  allowed_states:
    vic: 'Victoria'
```


## Configure multiple countries or states

### Global setting (affects all DataObjects using Addressable)

```yml
Symbiote\Addressable\Addressable:
  allowed_countries:
    au: 'Australia'
    nz: 'New Zealand'
  allowed_states:
    vic: 'Victoria'
    nsw: 'New South Wales'
```


### Local setting (affects the targetted DataObjects)
```yml
Page:
  extensions:
    - Symbiote\Addressable\Addressable
  allowed_countries:
    au: 'Australia'
    nz: 'New Zealand'
  allowed_states:
    vic: 'Victoria'
    nsw: 'New South Wales'
```

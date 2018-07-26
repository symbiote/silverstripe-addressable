# Advanced Configuration


## Configuration

```yml
Symbiote\Addressable\GeocodeService:
  google_api_url: 'https://maps.googleapis.com/maps/api/geocode/xml'
  google_api_key: 'API_KEY_HERE'
```

## Lock to one country or state

You can lock down Addressable to only use 1 country or 1 state by configuring it like so:

```yml
Symbiote\Addressable\Addressable:
  allowed_countries:
    au: 'Australia'
  allowed_states:
    vic: 'Victoria'
```

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

## Change regex to validate postcode

```yml
Symbiote\Addressable\Addressable:
  postcode_regex: '/^[0-9A-Za-z]+$/'
```

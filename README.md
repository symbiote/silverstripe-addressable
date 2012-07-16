SilverStripe Addressable Module
===============================

The Addressable module adds address fields to an object, and also has support
for automatic geocoding.

Maintainer Contact
------------------
*  Andrew Short (<andrew@silverstripe.com.au>)

Requirements
------------
*  SilverStripe 3.0+

Documentation
-------------

Quick Usage Overview
--------------------

In order to add simple address fields (address, suburb, city, postcode and
country) to an object, simply apply to `Addressable` extension:

    Object::add_extension('Object', 'Addressable');

In order to then render the full address into a template, you can use either
`$FullAddress` to return a simple string, or `$FullAddressHTML` to render
the address into a HTML `<address>` tag.

You can define a global set of allowed states or countries using
`Addressable::set_allowed_states()` and `::set_allowed_countries()`
respectively. These can also be set per-instance using `setAllowedStates()` and
`setAllowedCountries()`.

If a single string is provided as a value, then this will be set as the field
for all new objects and the user will not be presented with an input field. If
the value is an array, the user will be presented with a dropdown field.

To add automatic geocoding to an `Addressable` object when the address is
changed, simple apply the `Geocodable` extension:

    Object::add_extension('Object', 'Geocodable');

This will then use the Google Maps API to translate the address into a latitude
and longitude on save, and save it into the `Lat` and `Lng` fields.
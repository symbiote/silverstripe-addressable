SilverStripe Addressable Module
===============================

The Addressable module adds address fields to an object, and also has support
for automatic geocoding.

Maintainer Contact
------------------
*  Andrew Short (<andrew@silverstripe.com.au>)

Requirements
------------
*  SilverStripe 2.4+

Installation
------------

Extract the contents of the zip-file, rename the folder to `addressable`
and copy it to your website root.
Add the object extensions to your `mysite/_config.php` as needed 
(see Quick Usage Overview below).
Then run a `mysite.com/dev/build?flush=1` to rebuild the database.

Documentation
-------------

Quick Usage Overview: Adressable Extension
-----------------------------------------

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

Quick Usage Overview: GoogleMapsAdressable Extension
---------------------------------------------------

GoogleMapsAddressable adds the ability to render a dynamic Google Maps map
with a single marker for the given address on an object.
You can setup the UI of the Google map in the CMS 
(width, height, map type, controls, etc.)

It extends the `Addressable` extension, so you can as well use all the
functionality described above.

Simply apply the `GoogleMapsAddressable` and `Geocodable` extension to your Object, by adding
the following line to your `mysite/_config.php`:

    Object::add_extension('Object', 'GoogleMapsAddressable');
    Object::add_extension('Object', 'Geocodable');

Then run a `mysite.com/dev/build?flush=1` to rebuild the database.

Now you can add the Google map by adding `$GoogleMapsAddressMap` to your template.
`GoogleMapsAddressable` uses the `GoogleMapsAddressMap.ss` template. 
This template is a starting point with all variables and functions available.
You can customize the code, by creating your own template in your themes folder:
`themes/MYTHEME/templates/GoogleMapsAddressMap.ss`


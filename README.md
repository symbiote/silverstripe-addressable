# Addressable

[![Build Status](https://travis-ci.org/symbiote/silverstripe-addressable.svg?branch=master)](https://travis-ci.org/symbiote/silverstripe-addressable)
[![Latest Stable Version](https://poser.pugx.org/symbiote/silverstripe-addressable/version.svg)](https://github.com/symbiote/silverstripe-addressable/releases)
[![Latest Unstable Version](https://poser.pugx.org/symbiote/silverstripe-addressable/v/unstable.svg)](https://packagist.org/packages/symbiote/silverstripe-addressable)
[![Total Downloads](https://poser.pugx.org/symbiote/silverstripe-addressable/downloads.svg)](https://packagist.org/packages/symbiote/silverstripe-addressable)
[![License](https://poser.pugx.org/symbiote/silverstripe-addressable/license.svg)](https://github.com/symbiote/silverstripe-addressable/blob/master/LICENSE.md)

Adds address fields to a DataObject and also has support for automatic geocoding of the provided address.

![CMS screenshot](https://user-images.githubusercontent.com/3859574/43246926-8b218be2-90f6-11e8-9929-72192e23fc81.png)

## Composer Install

```
composer require symbiote/silverstripe-addressable:~4.0
```

## Requirements

* SilverStripe 4.0+

## Documentation

* [Quick Start](docs/en/quick-start.md)
* [Advanced Usage](docs/en/advanced-usage.md)
* [License](LICENSE.md)
* [Contributing](CONTRIBUTING.md)

## Changes from SilverStripe 3.X

* `GoogleGeocoding` changed class name to `Symbiote\Addressable\GeocodeService`

## Credits

* [Mark Taylor](https://github.com/symbiote/silverstripe-addressable/commit/7eb2f81c66502093c82c293943f43de9154ad807) for adding the ability to easily embed a map with AddressMap
* [Nic](https://github.com/muskie9) for writing tests for this module
* [AJ Short](https://github.com/ajshort) for initially writing this module

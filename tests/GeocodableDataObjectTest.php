<?php

namespace Symbiote\Addressable\Tests;

use Symbiote\Addressable\Addressable;
use Symbiote\Addressable\Geocodable;
use SilverStripe\ORM\DataObject;

class GeocodableDataObjectTest extends DataObject
{
    private static $extensions = array(
        Addressable::class, // Geocodable depends on `isAddressChanged` function from this extension
        Geocodable::class,
    );
}

<?php

namespace Symbiote\Addressable\Tests;

use Symbiote\Addressable\Addressable;
use SilverStripe\ORM\DataObject;

class AddressableDataObjectTest extends DataObject
{
    private static $extensions = array(
        Addressable::class,
    );
}

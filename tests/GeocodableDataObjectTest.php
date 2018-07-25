<?php

class GeocodableDataObjectTest extends DataObject {
    private static $extensions = array(
        'Addressable', // Geocodable depends on `isAddressChanged` function from this extension
        'Geocodable',
    );
}

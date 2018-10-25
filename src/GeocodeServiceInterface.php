<?php

namespace Symbiote\Addressable;

/**
 * Interface for service classes for geocoding addresses using.
 *
 * @package silverstripe-addressable
 */
interface GeocodeServiceInterface {

    /**
     * Convert an address into a latitude and longitude.
     *
     * @param string $address The address to geocode.
     * @param string $region  An optional two letter region code.
     * @return array An associative array with lat and lng keys.
     */
    public function addressToPoint($address, $region = '');

}

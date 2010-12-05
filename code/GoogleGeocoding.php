<?php
/**
 * A utility class for geocoding addresses using the google maps API.
 *
 * @package silverstripe-addressable
 */
class GoogleGeocoding {

	const API_URL = 'http://maps.googleapis.com/maps/api/geocode/xml';

	/**
	 * Convert an address into a latitude and longitude.
	 *
	 * @param  string $address The address to geocode.
	 * @param  string $region  An optional two letter region code.
	 * @return array An associative array with lat and lng keys.
	 */
	public static function address_to_point($address, $region = null) {
		$service = new RestfulService(self::API_URL);
		$service->setQueryString(array(
			'address' => $address,
			'sensor'  => 'false',
			'region'  => $region
		));
		$response = $service->request()->simpleXML();

		if ($response->status != 'OK') {
			return false;
		}

		$location = $response->result->geometry->location;
		return array(
			'lat' => (float) $location->lat,
			'lng' => (float) $location->lng
		);
	}

}
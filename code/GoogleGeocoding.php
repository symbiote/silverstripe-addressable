<?php
/**
 * A utility class for geocoding addresses using the google maps API.
 *
 * @package silverstripe-addressable
 */
class GoogleGeocoding {

	/**
	 * Convert an address into a latitude and longitude.
	 *
	 * @param string $address The address to geocode.
	 * @param string $region  An optional two letter region code.
	 * @return array An associative array with lat and lng keys.
	 */
	public static function address_to_point($address, $region = null) {
		// Get the URL for the Google API
		$url = Config::inst()->get('GoogleGeocoding', 'google_api_url');
		$key = Config::inst()->get('GoogleGeocoding', 'google_api_key');

		// Query the Google API
		$service = new RestfulService($url);
		$service->setQueryString(array(
			'address' => $address,
			'sensor'  => 'false',
			'region'  => $region,
			'key'		=> $key
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

<<<<<<< HEAD
}
=======

	/**
	 * Convert a latitude and longitude into an address.
	 *
	 * @param  string $address The address to geocode.
	 * @param  string $region  An optional two letter region code.
	 * @return array An associative array with lat and lng keys.
	 */
	public static function point_to_address($lat, $lng, $region = null) {
		// Get the URL for the Google API
		$url = Config::inst()->get('GoogleGeocoding', 'google_api_url');
		$key = Config::inst()->get('GoogleGeocoding', 'google_api_key');

		// Query the Google API
		$service = new RestfulService($url);
		$service->setQueryString(array(
			'latlng' => "{$lat},{$lng}",
			'sensor'  => 'false',
			'result_type' => 'street_address',
			'key'	=> $key
		));
		$response = $service->request()->simpleXML();

		if ($response->status != 'OK') {
			return false;
		}

		$location = $response->result->address_component;

		return array(
			'StreetNumber'  => self::get_long_name_of('street_number', $location),
			'Address'  => self::get_long_name_of('route', $location),
			'Suburb'   => self::get_long_name_of('locality', $location),
			'State'    => self::get_long_name_of('administrative_area_level_1', $location),
			'Postcode' => self::get_long_name_of('postal_code', $location),
			'Country'  => self::get_long_name_of('country', $location),
			'CountryCode'  => self::get_short_name_of('country', $location)
		);
	}

	public static function get_long_name_of($type, $xml) {
		foreach ($xml as $simplexml) {
			if((is_array($simplexml->type) && in_array($type, $simplexml->type)) || $type == $simplexml->type) {
				return $simplexml->long_name->__toString();
			}
		}
	}

	public static function get_short_name_of($type, $xml) {
		foreach ($xml as $simplexml) {
			if((is_array($simplexml->type) && in_array($type, $simplexml->type)) || $type == $simplexml->type) {
				return $simplexml->short_name->__toString();
			}
		}
	}
}
>>>>>>> 36a97ba... Add reverse geocoding

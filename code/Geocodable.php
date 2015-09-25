<?php
/**
 * Adds automatic geocoding to a {@link Addressable} object. Uses the Google
 * Maps API to save latitude and longitude on write.
 *
 * @package silverstripe-addressable
 */
class Geocodable extends DataExtension {

	private static $db = array(
		'Lat' => 'Decimal(9,5)',
		'Lng' => 'Decimal(9,5)'
	);

	public function onBeforeWrite() {

		if ($this->owner->isAddressChanged()) {

			$address = $this->owner->getFullAddress();
			$region  = strtolower($this->owner->Country);

			if(!$point = GoogleGeocoding::address_to_point($address, $region)) {
				return;
			}

			$this->owner->Lat = $point['lat'];
			$this->owner->Lng = $point['lng'];
		}
		if ($this->owner->isGeocodeChanged()) {

			$lat = $this->owner->Lat;
			$lng  = $this->owner->Lng;

			if(!$address = GoogleGeocoding::point_to_address($lat, $lng)) {
				return;
			}

			$this->owner->Address	= $address['StreetNumber'] . ' ' . $address['Address'];
			$this->owner->Suburb	= $address['Suburb'];
			$this->owner->State		= $address['State'];
			$this->owner->Postcode	= $address['Postcode'];
			$this->owner->Country	= $address['CountryCode'];
		}
	}

	public function updateCMSFields(FieldList $fields) {
		//$fields->removeByName('Lat');
		//$fields->removeByName('Lng');
	}

	public function updateFrontEndFields(FieldList $fields) {
		$this->updateCMSFields($fields);
	}

}

<?php
/**
 * Adds address fields to an object that can be geocoded and plotted on an interactive Google Map.
 * UI of Google Map can be defined in the CMS (width, height, map type, controls, etc.).
 *
 * Features:
 * - $GoogleMapAddressMap: Renders a dynamic Google Map with a single marker for the given address
 * - $GoogleRoutingLink: Renders a link with routing information, with the given address as target
 * 
 * ---
 * 
 * This Extension can be added to SiteTree (e.g. SiteTree, Page or any custom Page)
 * adding the follwoing lines to mysite/_config.php:
 * 
 * Object::add_extension('Object', 'GoogleMapsAddressable');
 * Object::add_extension('Object', 'Geocodable');
 * 
 * ---
 * 
 * You can use the following variables in your templates:
 * 
 * $GoogleMapsAddressMap
 * -> Renders a dynamic JavaScript Google Map, with controls as set up in the CMS
 *    (using template GoogleMapsAddressMap.ss)
 *
 * $GoogleRoutingLink
 * -> Returns a link to Google Maps Routing service, with the given address set as target
 *
 * $AddressIsSet
 * -> Returns true if any of the address fields is set (Address, Suburb, State or Postcode)
 *    You can use in an <% if %> clause
 * 
 * ---
 * 
 * This extensions also integrates with the {@link Geocoding} extension to
 * save co-ordinates on object write.
 *
 * @package silverstripe-addressable
 */
class GoogleMapsAddressable extends Addressable {

	public function extraStatics() {
		
		$db = parent::extraStatics();
		
		$db = array('db' => array_merge(
			$db['db'], 
			array(
				'ShowGoogleMap' => 'Boolean',
				'ShowRoutingLink' => 'Boolean',
				'MapWidth' => 'Int',
				'MapHeight' => 'Int',
				'MapZoom' => 'Int',
				'MapPanControl' => 'Boolean',
				'MapZoomControl' => 'Boolean',
				'MapZoomStyle' => "Enum('SMALL, LARGE, DEFAULT', 'DEFAULT')",
				'MapTypeControl' => 'Boolean',
				'MapTypeStyle' => "Enum('HORIZONTAL_BAR, DROPDOWN_MENU, DEFAULT', 'DEFAULT')",
				'MapTypeId' => "Enum('HYBRID, ROADMAP, SATELLITE, TERRAIN', 'HYBRID')",
				'MapScaleControl' => 'Boolean',
				'MapStreetViewControl' => 'Boolean',
				'MapOverviewMapControl' => 'Boolean'
			)
		));
		
		return $db;
	}

	public function populateDefaults() {
		
		$this->owner->ShowGoogleMap = 1;
		$this->owner->ShowRoutingLink = 1;
		
		$this->owner->MapWidth = 480;
		$this->owner->MapHeight = 320;
		
		$this->owner->MapZoom = 13;
		$this->owner->MapPanControl = 0;
		$this->owner->MapZoomControl = 1;
		$this->owner->MapZoomStyle = 'DEFAULT';
		$this->owner->MapTypeControl = 1;
		$this->owner->MapTypeStyle = 'DEFAULT';
		$this->owner->MapTypeId = 'HYBRID';
		$this->owner->MapScaleControl = 0;
		$this->owner->MapStreetViewControl = 0;
		$this->owner->MapOverviewMapControl = 0;
	}

	/**
	 * @return array
	 */
	protected function getAddressFields() {
		
		$fields = parent::getAddressFields();		
		
		// Add Map Setup
		$fields[] = new HeaderField('MapSetupHeader', _t('GoogleMapsAddressable.MAPSETUPHEADER', 'Map Setup'));
		$fields[] = new CheckboxField('ShowGoogleMap', _t('GoogleMapsAddressable.SHOWGOOGLEMAP', 'Show Google Map'));
		$fields[] = new CheckboxField('ShowRoutingLink', _t('GoogleMapsAddressable.SHOWROUTINGLINK', 'Show Routing Link'));
		$fields[] = new NumericField('MapWidth', _t('GoogleMapsAddressable.MAPWIDTH', 'Map width'));
		$fields[] = new NumericField('MapHeight', _t('GoogleMapsAddressable.MAPHEIGHT', 'Map height'));
		$fields[] = new NumericField('MapZoom', _t('GoogleMapsAddressable.MAPZOOM', 'Zoom Factor of Map (1 = worldview - 21 = street view)'));
		
		// Add Dynamic Map Setup
		$fields[] = new HeaderField('DynamicMapSetupHeader', _t('GoogleMapsAddressable.DYNAMICMAPSETUPHEADER', 'Dynamic Map Setup (Javascript Google Map)'));
		$fields[] = new CheckboxField('MapPanControl', _t('GoogleMapsAddressable.MAPPANCONTROL', 'Show Pan Control'));
		$fields[] = new CheckboxField('MapZoomControl', _t('GoogleMapsAddressable.MAPZOOMCONTROL', 'Show Zoom Control'));
		
		$zoomTypes = singleton($this->owner->ClassName)->dbObject('MapZoomStyle')->enumValues();
		$fields[] = new DropdownField('MapZoomStyle', _t('GoogleMapsAddressable.MAPZOOMSTYLE', 'Zoom Control Styling'), $zoomTypes);
				
		$fields[] = new CheckboxField('MapTypeControl', _t('GoogleMapsAddressable.MAPTYPECONTROL', 'Show Map Type Control'));
		
		$typeTypes = singleton($this->owner->ClassName)->dbObject('MapTypeStyle')->enumValues();
		$fields[] = new DropdownField('MapTypeStyle', _t('GoogleMapsAddressable.MAPTYPESTYLE', 'Map Type Control Styling'), $typeTypes);
		
		$typeIds = singleton($this->owner->ClassName)->dbObject('MapTypeId')->enumValues();
		$fields[] = new DropdownField('MapTypeId', _t('GoogleMapsAddressable.MAPTYPEID', 'Map Type'), $typeIds);
		
		$fields[] = new CheckboxField('MapScaleControl', _t('GoogleMapsAddressable.MAPSCALECONTROL', 'Show Scale Control'));
		$fields[] = new CheckboxField('MapStreetViewControl', _t('GoogleMapsAddressable.MAPSTREETVIEWCONTROL', 'Show StreetView Control'));
		$fields[] = new CheckboxField('MapOverviewMapControl', _t('GoogleMapsAddressable.MAPOVERVIEWMAPCONTROL', 'Show Overview Map Control'));

		return $fields;
	}
	
	/**
	 * @return bool
	 */
	public function AddressIsSet() {
		return (
			$this->owner->Address
			|| $this->owner->Suburb
			|| $this->owner->State
			|| $this->owner->Postcode
		);
	}
	
	/**
	 * Returns a dynamic google map with a marker for the given address
	 * Controls and map setup is set using the CMS
	 * 
	 * The Map is rendered using the GoogleMapsAddressMap.ss template
	 * You can create a custom template in your themes folder: themes/MYTHEME/templates/GoogleMapsAddressMap.ss
	 *
	 * @return string
	 */
	public function GoogleMapsAddressMap() {
		$data = $this->owner->customise(array(
			'AddressEncoded' => rawurlencode($this->getFullAddress())
		));
		return $data->renderWith('GoogleMapsAddressMap');
	}

	/**
	 * Returns a link to googles Routing Service, with the address as target address
	 * 
	 * @return String
	 */
	public function GoogleRoutingLink() {
		return "http://maps.google.com/maps?daddr=".rawurlencode($this->owner->getFullAddress());
	}

}
<?php
/**
 * Adds address fields to an object, as well as fields to manage them.
 * 
 * 
 * ---
 * 
 * This Extension can be added to SiteTree (e.g. SiteTree, Page or any custom Page)
 * adding the follwoing line to mysite/_config.php:
 * 
 * Object::add_extension('MyDataObject', 'Addressable');
 * 
 * ---
 * 
 * You can use the following variables in your templates:
 * 
 * $Address
 * -> Renders the Address in HTML (using Address.ss template)
 * 
 * $AddressMap(width,height)
 * -> Renders a static Google Map (using AddressMap.ss template)
 * 
 * $DynamicAddressMap(width,height)
 * -> Renders a dynamic JavaScript Google Map, with controls as set up on the page
 * (using template DynamicAddressMap)
 * 
 * ---
 * 
 * This extensions also integrates with the {@link Geocoding} extension to
 * save co-ordinates on object write.
 *
 * @package silverstripe-addressable
 */
class Addressable extends DataObjectDecorator {

	protected static $allowed_states;
	protected static $allowed_countries;
	protected static $postcode_regex= '/^[0-9]+$/';

	protected $allowedStates;
	protected $allowedCountries;
	protected $postcodeRegex;

	/**
	 * Sets the default allowed states for new instances.
	 *
	 * @param null|string|array $states
	 * @see   Addressable::setAllowedStates
	 */
	public static function set_allowed_states($states) {
		self::$allowed_states = $states;
	}

	/**
	 * Sets the default allowed countries for new instances.
	 *
	 * @param null|string|array $countries
	 * @see   Addressable::setAllowedCountries
	 */
	public static function set_allowed_countries($countries) {
		self::$allowed_countries = $countries;
	}

	/**
	 * Sets the default postcode regex for new instances.
	 *
	 * @param string $regex
	 */
	public static function set_postcode_regex($regex) {
		self::$postcode_regex = $regex;
	}

	public function __construct() {
		$this->allowedStates    = self::$allowed_states;
		$this->allowedCountries = self::$allowed_countries;
		$this->postcodeRegex    = self::$postcode_regex;

		parent::__construct();
	}

	public function extraStatics() {
		return array('db' => array(
			'Address'  => 'Varchar(255)',
			'Suburb'   => 'Varchar(64)',
			'State'    => 'Varchar(64)',
			'Postcode' => 'Varchar(10)',
			'Country'  => 'Varchar(2)',
			'CompanyName' => 'Varchar',
			'Telephone' => 'Varchar',
			'Fax' => 'Varchar',
			'Email' => 'Varchar',
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
		));
	}

	public function updateCMSFields($fields) {
		if ($fields->fieldByName('Root.Content')) {
			$tab = 'Root.Content.Address';
		} else {
			$tab = 'Root.Address';
		}
		$fields->addFieldsToTab($tab, $this->getAddressFields());
		$fields->fieldByName($tab)->setTitle(_t('Addressable.TABTITLE', 'Address Map'));
	}

	public function updateFrontEndFields($fields) {
		foreach ($this->getAddressFields() as $field) $fields->push($field);
	}

	public function populateDefaults() {
		if (is_string($this->allowedStates)) {
			$this->owner->State = $this->allowedStates;
		}

		if (is_string($this->allowedCountries)) {
			$this->owner->Country = $this->allowedCountries;
		}
		
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
		$fields = array(
			new HeaderField('CompanyHeader', _t('Addressable.COMPANYHEADER', 'Company Name')),
			new TextField('CompanyName', _t('Addressable.COMPANYNAME', 'Company Name')),
			new HeaderField('AddressHeader', _t('Addressable.ADDRESSHEADER', 'Address')),
			new TextField('Address', _t('Addressable.ADDRESS', 'Street address')),
			new TextField('Suburb', _t('Addressable.SUBURB', 'Suburb'))
		);

		$label = _t('Addressable.STATE', 'State');
		if (is_array($this->allowedStates)) {
			$fields[] = new DropdownField('State', $label, $this->allowedStates);
		} elseif (!is_string($this->allowedStates)) {
			$fields[] = new TextField('State', $label);
		}

		$postcode = new RegexTextField('Postcode', _t('Addressable.POSTCODE', 'Postcode'));
		$postcode->setRegex($this->postcodeRegex);
		$fields[] = $postcode;

		$label = _t('Addressable.COUNTRY', 'Country');
		if (is_array($this->allowedCountries)) {
			$fields[] = new DropdownField('Country', $label, $this->allowedCountries);
		} elseif (!is_string($this->allowedCountries)) {
			$fields[] = new CountryDropdownField('Country', $label);
		}
		
		// Add Additional contact data (not used for geocoding)
		$fields[] = new HeaderField('ContactHeader', _t('Addressable.CONTACTDATA', 'Contact Details'));
		$fields[] = new TextField('Telephone', _t('Addressable.TELEPHONE', 'Telephone'));
		$fields[] = new TextField('Fax', _t('Addressable.FAX', 'Fax'));
		$fields[] = new TextField('Email', _t('Addressable.EMAIL', 'Email'));
		
		
		// Add Map Setup
		$fields[] = new HeaderField('MapSetupHeader', _t('Addressable.MAPSETUPHEADER', 'Map Setup'));
		$fields[] = new NumericField('MapWidth', _t('Addressable.MAPWIDTH', 'Map width'));
		$fields[] = new NumericField('MapHeight', _t('Addressable.MAPHEIGHT', 'Map height'));
		
		// Add Dynamic Map Setup
		$fields[] = new HeaderField('DynamicMapSetupHeader', _t('Addressable.DYNAMICMAPSETUPHEADER', 'Dynamic Map Setup (Javascript Google Map)'));
		$fields[] = new NumericField('MapZoom', _t('Addressable.MAPZOOM', 'Zoom Factor of Map (1 = worldview - 21 = street view)'));
		$fields[] = new CheckboxField('MapPanControl', _t('Addressable.MAPPANCONTROL', 'Show Pan Control'));
		$fields[] = new CheckboxField('MapZoomControl', _t('Addressable.MAPZOOMCONTROL', 'Show Zoom Control'));
		
		$zoomTypes = singleton($this->owner->ClassName)->dbObject('MapZoomStyle')->enumValues();
		$fields[] = new DropdownField('MapZoomStyle', _t('Addressable.MAPZOOMSTYLE', 'Zoom Control Styling'), $zoomTypes);
				
		$fields[] = new CheckboxField('MapTypeControl', _t('Addressable.MAPTYPECONTROL', 'Show Map Type Control'));
		
		$typeTypes = singleton($this->owner->ClassName)->dbObject('MapTypeStyle')->enumValues();
		$fields[] = new DropdownField('MapTypeStyle', _t('Addressable.MAPTYPESTYLE', 'Map Type Control Styling'), $typeTypes);
		
		$typeIds = singleton($this->owner->ClassName)->dbObject('MapTypeId')->enumValues();
		$fields[] = new DropdownField('MapTypeId', _t('Addressable.MAPTYPEID', 'Map Type'), $typeIds);
		
		$fields[] = new CheckboxField('MapScaleControl', _t('Addressable.MAPSCALECONTROL', 'Show Scale Control'));
		$fields[] = new CheckboxField('MapStreetViewControl', _t('Addressable.MAPSTREETVIEWCONTROL', 'Show StreetView Control'));
		$fields[] = new CheckboxField('MapOverviewMapControl', _t('Addressable.MAPOVERVIEWMAPCONTROL', 'Show Overview Map Control'));

		return $fields;
	}

	/**
	 * @return bool
	 */
	public function hasAddress() {
		return (
			$this->owner->Address
			&& $this->owner->Suburb
			&& $this->owner->State
			&& $this->owner->Postcode
			&& $this->owner->Country
		);
	}

	/**
	 * Returns the full address as a simple string.
	 *
	 * @return string
	 */
	public function getFullAddress() {
		return sprintf('%s, %s, %s %d, %s',
			$this->owner->Address,
			$this->owner->Suburb,
			$this->owner->State,
			$this->owner->Postcode,
			$this->getCountryName());
	}

	/**
	 * Returns the full address in a simple HTML template.
	 *
	 * @return string
	 */
	public function getFullAddressHTML() {
		return $this->owner->renderWith('Address');
	}

	/**
	 * Returns a static google map of the address, linking out to the address.
	 * 
	 * @return string
	 */
	public function AddressMap() {
		$data = $this->owner->customise(array(
			'AddressEncoded' => rawurlencode($this->getFullAddress())
		));
		return $data->renderWith('AddressMap');
	}
	
	/**
	 * Returns a dynamic google map of the address
	 * Controls and map setup is set using the CMS
	 * 
	 * The Map is rendered using the DynamicAddressMap.ss template
	 * You can create a custom template in your themes/MYTHEME/templates/ folder
	 * @return string
	 */
	public function DynamicAddressMap() {
		$data = $this->owner->customise(array(
			'AddressEncoded' => rawurlencode($this->getFullAddress())
		));
		return $data->renderWith('DynamicAddressMap');
	}

	/**
	 * Returns the country name (not the 2 character code).
	 *
	 * @return string
	 */
	public function getCountryName() {
		return Geoip::countrycode2name($this->owner->Country);
	}

	/**
	 * Returns TRUE if any of the address fields have changed.
	 *
	 * @param  int $level
	 * @return bool
	 */
	public function isAddressChanged($level = 1) {
		$fields  = array('Address', 'Suburb', 'State', 'Postcode', 'Country');
		$changed = $this->owner->getChangedFields(false, $level);

		foreach ($fields as $field) {
			if (array_key_exists($field, $changed)) return true;
		}

		return false;
	}

	/**
	 * Returns a link to googles Routing Service, with the Distributor address as destination address
	 * 
	 * @return String
	 */
	public function GoogleRoutingLink() {
		return "http://maps.google.com/maps?daddr=".rawurlencode($this->owner->getFullAddress());
	}

	/**
	 * Sets the states that a user can select. By default they can input any
	 * state into a text field, but if you set an array it will be replaced with
	 * a dropdown field.
	 *
	 * @param array $states
	 */
	public function setAllowedStates($states) {
		$this->allowedStates = $states;
	}

	/**
	 * Sets the countries that a user can select. There are three possible
	 * values:
	 *
	 * <ul>
	 *   <li>null: Present a text box to the user.</li>
	 *   <li>string: Set the country to the two letter country code passed, and
	 *       do not allow users to select a country.</li>
	 *   <li>array: Allow users to select from the list of passed countries.</li>
	 * </ul>
	 *
	 * @param null|string|array $states
	 */
	public function setAllowedCountries($countries) {
		$this->allowedCountries = $countries;
	}

	/**
	 * Sets a regex that an entered postcode must match to be accepted. This can
	 * be set to NULL to disable postcode validation and allow any value.
	 *
	 * The postcode regex defaults to only accepting numerical postcodes.
	 *
	 * @param string $regex
	 */
	public function setPostcodeRegex($regex) {
		$this->postcodeRegex = $regex;
	}

}
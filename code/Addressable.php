<?php
/**
 * Adds simple address fields to an object, as well as fields to manage them.
 *
 * This extensions also integrates with the {@link Geocoding} extension to
 * save co-ordinates on object write.
 *
 * @todo    Support non-numerical postcodes.
 * @package silverstripe-addressable
 */
class Addressable extends DataObjectDecorator {

	protected static $allowed_states;
	protected static $allowed_countries;

	protected $allowedStates;
	protected $allowedCountries;

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

	public function __construct() {
		$this->allowedStates    = self::$allowed_states;
		$this->allowedCountries = self::$allowed_countries;

		parent::__construct();
	}

	public function extraStatics() {
		return array('db' => array(
			'Address'  => 'Varchar(255)',
			'Suburb'   => 'varchar(64)',
			'State'    => 'Varchar(64)',
			'Postcode' => 'Int',
			'Country'  => 'Varchar(2)'
		));
	}

	public function updateCMSFields($fields) {
		$fields->addFieldsToTab('Root.Content.Address', $this->getAddressFields());
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
	}

	/**
	 * @return array
	 */
	protected function getAddressFields() {
		$fields = array(
			new HeaderField('AddressHeader', _t('Addressable.ADDRESSHEADER', 'Address')),
			new TextField('Address', _t('Addressable.ADDRESS', 'Address')),
			new TextField('Suburb', _t('Addressable.SUBURB', 'Suburb')));

		$label = _t('Addressable.STATE', 'State');
		if (is_array($this->allowedStates)) {
			$fields[] = new DropdownField('State', $label, $this->allowedStates);
		} elseif (!is_string($this->allowedStates)) {
			$fields[] = new TextField('State', $label);
		}

		$fields[] = new NumericField(
			'Postcode', _t('Addressable.POSTCODE', 'Postcode')
		);

		$label = _t('Addressable.COUNTRY', 'Country');
		if (is_array($this->allowedCountries)) {
			$fields[] = new DropdownField('Country', $label, $this->allowedCountries);
		} elseif (!is_string($this->allowedCountries)) {
			$fields[] = new CountryDropdownField('Country', $label);
		}

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
	 * @param  int $width
	 * @param  int $height
	 * @return string
	 */
	public function AddressMap($width, $height) {
		$data = $this->owner->customise(array(
			'Width'  => $width,
			'Height' => $height
		));
		return $data->renderWith('AddressMap');
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

}
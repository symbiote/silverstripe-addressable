<?php

namespace Symbiote\Addressable;

use Exception;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\i18n\Data\Intl\IntlLocales;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBHTMLText;
use Symbiote\Addressable\Forms\RegexTextField;
use Symbiote\Addressable\GoogleGeocodeService;

/**
 * Adds simple address fields to an object, as well as fields to manage them.
 *
 * This extensions also integrates with the {@link Geocoding} extension to
 * save co-ordinates on object write.
 *
 * @package silverstripe-addressable
 */
class Addressable extends DataExtension
{
    private static $db = array(
        'Address'  => 'Varchar(255)',
        'Suburb'   => 'Varchar(64)',
        'State'    => 'Varchar(64)',
        'Postcode' => 'Varchar(10)',
        'Country'  => 'Varchar(2)'
    );

    /**
     * Define an array of states that the user can select from.
     * If no states are defined, a user can type in any plain text for their state.
     * If only 1 state is defined, that will be the default populated value.
     *
     * @var array
     * @config
     */
    private static $allowed_states = [];

    /**
     * Define an array of countries that the user can select from.
     * If only 1 country is defined, that will be the default populated value.
     *
     * @var array
     * @config
     */
    private static $allowed_countries = [];

    /**
     * @var string
     * @config
     */
    private static $postcode_regex = '/^[0-9]+$/';

    public function __construct()
    {
        parent::__construct();

        // Throw exception for deprecated config
        if (Config::inst()->get('Addressable', 'set_postcode_regex') ||
            Config::inst()->get(__CLASS__, 'set_postcode_regex')) {
            throw new Exception('Addressable config "set_postcode_regex" is deprecated in favour of using YML config "postcode_regex"');
        }
    }

    public function updateCMSFields(FieldList $fields)
    {
        if ($fields->hasTabSet()) {
            $fields->addFieldsToTab('Root.Address', $this->getAddressFields());
        } else {
            $newFields = $this->getAddressFields();
            foreach ($newFields as $field) {
                $fields->push($field);
            }
        }
    }

    public function updateFrontEndFields(FieldList $fields)
    {
        $fields->merge($this->getAddressFields());
    }

    public function populateDefaults()
    {
        $allowedStates = $this->owner->getAllowedStates();
        if (is_array($allowedStates) &&
            count($allowedStates) === 1) {
            reset($allowedStates);
            $this->owner->State = key($allowedStates);
        }

        $allowedCountries = $this->owner->getAllowedCountries();
        if (is_array($allowedCountries) &&
            count($allowedCountries) === 1) {
            reset($allowedCountries);
            $this->owner->Country = key($allowedCountries);
        }
    }

    /**
     * @return bool
     */
    public function hasAddress()
    {
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
    public function getFullAddress()
    {
        $parts = array(
            $this->owner->Address,
            $this->owner->Suburb,
            $this->owner->State,
            $this->owner->Postcode,
            $this->owner->getCountryName()
        );

        return implode(', ', array_filter($parts));
    }

    /**
     * Returns the full address in a simple HTML template.
     *
     * @return DBHTMLText
     */
    public function getFullAddressHTML()
    {
        return $this->owner->renderWith('Symbiote/Addressable/Address');
    }

    /**
     * Returns a static google map of the address, linking out to the address.
     *
     * @param int $width (optional)
     * @param int $height (optional)
     * @param int $scale (optional)
     * @return DBHTMLText
     */
    public function AddressMap($width = 320, $height = 240, $scale = 1)
    {
        $data = $this->owner->customise(array(
            'Width'    => $width,
            'Height'   => $height,
            'Scale'    => $scale,
            'Address'  => rawurlencode($this->getFullAddress()),
            'Key'      => Config::inst()->get(GoogleGeocodeService::class, 'google_api_key')
        ));
        return $data->renderWith('Symbiote/Addressable/AddressMap');
    }

    /**
     * Returns the country name (not the 2 character code).
     *
     * @return string
     */
    public function getCountryName()
    {
        return IntlLocales::singleton()->countryName($this->owner->Country);
    }

    /**
     * Returns TRUE if any of the address fields have changed.
     *
     * @param int $level
     * @return bool
     */
    public function isAddressChanged($level = 1)
    {
        $fields  = array('Address', 'Suburb', 'State', 'Postcode', 'Country');
        $changed = $this->owner->getChangedFields(false, $level);

        foreach ($fields as $field) {
            if (array_key_exists($field, $changed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * NOTE:
     *
     * This was made private as you should *probably* be using "updateAddressFields" to manipulate
     * these fields (if at all).
     *
     * If this doesn't end up being the case, feel free to make a PR and change this back to "public".
     *
     * @return array
     */
    private function getAddressFields($_params = array())
    {
        $params = array_merge(
            array(
                'includeHeader' => true,
            ),
            (array) $_params
        );

        $fields = array(
            TextField::create('Address', _t('Addressable.ADDRESS', 'Address')),
            TextField::create('Suburb', _t('Addressable.SUBURB', 'Suburb'))
        );

        if ($params['includeHeader']) {
            array_unshift(
                $fields,
                HeaderField::create('AddressHeader', _t('Addressable.ADDRESSHEADER', 'Address'))
            );
        }


        // Get state field
        $label = _t('Addressable.STATE', 'State');
        $allowedStates = $this->owner->getAllowedStates();
        if (count($allowedStates) >= 1) {
            // If allowed states are restricted, only allow those
            $fields[] = DropdownField::create('State', $label, $allowedStates);
        } elseif (!$allowedStates) {
            // If no allowed states defined, allow the user to type anything
            $fields[] = TextField::create('State', $label);
        }

        // Get postcode field
        $postcode = RegexTextField::create('Postcode', _t('Addressable.POSTCODE', 'Postcode'));
        $postcode->setRegex($this->getPostcodeRegex());
        $fields[] = $postcode;

        // Get country field
        $fields[] = DropdownField::create(
            'Country',
            _t('Addressable.COUNTRY', 'Country'),
            $this->owner->getAllowedCountries()
        );

        $this->owner->extend("updateAddressFields", $fields);

        return $fields;
    }

    /**
     * Get the allowed states for this object
     *
     * @return array
     */
    public function getAllowedStates()
    {
        // Get states from extending object. (ie. Page, DataObject)
        $allowedStates = $this->owner->config()->allowed_states;
        if (is_array($allowedStates) &&
            $allowedStates) {
            return $allowedStates;
        }

        // Get allowed states global. If there are no specific rules on a Page/DataObject
        // fallback to what is configured on this extension
        $allowedStates = Config::inst()->get(__CLASS__, 'allowed_states');
        if (is_array($allowedStates) &&
            $allowedStates) {
            return $allowedStates;
        }
        return [];
    }

    /**
     * get the allowed countries for this object
     *
     * @return array
     */
    public function getAllowedCountries()
    {
        // Get allowed_countries from extending object. (ie. Page, DataObject)
        $allowedCountries = $this->owner->config()->allowed_countries;
        if (is_array($allowedCountries) &&
            $allowedCountries) {
            return $allowedCountries;
        }

        // Get allowed countries global. If there are no specific rules on a Page/DataObject
        // fallback to what is configured on this extension
        $allowedCountries = Config::inst()->get(__CLASS__, 'allowed_countries');
        if (is_array($allowedCountries) &&
            $allowedCountries) {
            return $allowedCountries;
        }

        // Finally, fallback to a full list of countries
        return IntlLocales::singleton()->config()->get('countries');
    }

    /**
     * @return string
     */
    private function getPostcodeRegex()
    {
        // Get postcode regex from extending object. (ie. Page, DataObject)
        $regex = $this->owner->config()->postcode_regex;
        if ($regex) {
            return $regex;
        }

        // Get postcode  regex global. If there are no specific rules on a Page/DataObject
        // fallback to what is configured on this extension
        $regex = Config::inst()->get(__CLASS__, 'postcode_regex');
        if ($regex) {
            return $regex;
        }

        return '';
    }
}

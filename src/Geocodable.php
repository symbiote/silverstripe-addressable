<?php

namespace Symbiote\Addressable;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\Injector\Injector;

/**
 * Adds automatic geocoding to a {@link Addressable} object. Uses the Google
 * Maps API to save latitude and longitude on write.
 *
 * @package silverstripe-addressable
 */
class Geocodable extends DataExtension
{
    /**
     * Disable geocoding for onBeforeWrite().
     *
     * You may want to do disable this when modifying lots
     * of data in a dev/task at once.
     *
     * @var boolean
     * @config
     */
    private static $is_geocodable = true;

    private static $db = array(
        'LatLngOverride' => 'Boolean',
        'Lat' => 'Decimal(10,7)',
        'Lng' => 'Decimal(10,7)'
    );

    public function onBeforeWrite()
    {
        $record = $this->getOwner();
        // Reset last error
        $record->__geocodable_exception = null;
        if (!Config::inst()->get(__CLASS__, 'is_geocodable')) {
            // Allow user-code to disable Geocodable. This was added
            // so that dev/tasks that write a *lot* of Geocodable records can
            // ignore this expensive logic.
            return;
        }
        if ($record->LatLngOverride) {
            // A CMS user disabled automatical retrieval of Lat/Lng
            // and most likely input their own values.
            return;
        }
        if (!$record->hasMethod('isAddressChanged') ||
            !$record->isAddressChanged()) {
            return;
        }

        $address = $record->getFullAddress();
        $region = strtolower($record->Country);

        $point = [];
        try {
            $point = Injector::inst()->get(GeocodeService::class)->addressToPoint($address, $region);
        } catch (GeocodeServiceException $e) {
            // Default behaviour is to ignore errors like ZERO_RESULTS or this just failing.
            $record->__geocodable_exception = $e;
            return;
        }
        if (!$point) {
            return;
        }

        $record->Lat = $point['lat'];
        $record->Lng = $point['lng'];
    }

    /**
     * @return GeocodeServiceException|null
     */
    public function getLastGeocodableException()
    {
        return $this->owner->__geocodable_exception;
    }

    public function updateCMSFields(FieldList $fields)
    {
        $record = $this->getOwner();
        $fields->removeByName(array('LatLngOverride', 'Lat', 'Lng'));

        // Adds Lat/Lng fields for viewing in the CMS
        $compositeField = CompositeField::create();
        $compositeField->push($overrideField = CheckboxField::create('LatLngOverride', 'Override Latitude and Longitude?'));
        $overrideField->setDescription('Check this box and save to be able to edit the latitude and longitude manually.');
        if ($record->Lng && $record->Lat) {
            $googleMapURL = 'https://maps.google.com/?q='.$record->Lat.','.$record->Lng;
            $googleMapDiv = '<div class="field"><label class="left" for="Form_EditForm_MapURL_Readonly">Google Map</label><div class="middleColumn"><a href="'.$googleMapURL.'" target="_blank">'.$googleMapURL.'</a></div></div>';
            $compositeField->push(LiteralField::create('MapURL_Readonly', $googleMapDiv));
        }
        if ($record->LatLngOverride) {
            $compositeField->push(TextField::create('Lat', 'Lat'));
            $compositeField->push(TextField::create('Lng', 'Lng'));
        } else {
            $compositeField->push(ReadonlyField::create('Lat_Readonly', 'Lat', $record->Lat));
            $compositeField->push(ReadonlyField::create('Lng_Readonly', 'Lng', $record->Lng));
        }
        if ($record->hasExtension('Addressable')) {
            // If using addressable, put the fields with it
            $fields->addFieldToTab('Root.Address', ToggleCompositeField::create('Coordinates', 'Coordinates', $compositeField));
        } elseif ($record instanceof SiteTree) {
            // If SIteTree but not using Addressable, put after 'Metadata' toggle composite field
            $fields->insertAfter($compositeField, 'ExtraMeta');
        } else {
            $fields->addFieldToTab('Root.Main', ToggleCompositeField::create('Coordinates', 'Coordinates', $compositeField));
        }
    }

    public function updateFrontEndFields(FieldList $fields)
    {
        $fields->removeByName(array('LatLngOverride', 'Lat', 'Lng'));
    }
}

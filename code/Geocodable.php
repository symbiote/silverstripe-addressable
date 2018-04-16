<?php

/**
 * Adds automatic geocoding to a {@link Addressable} object. Uses the Google
 * Maps API to save latitude and longitude on write.
 *
 * @package silverstripe-addressable
 */
class Geocodable extends DataExtension
{

    private static $db = array(
        'LatLngOverride' => 'Boolean',
        'Lat' => 'Decimal(10,7)',
        'Lng' => 'Decimal(10,7)'
    );

    public function onBeforeWrite()
    {
        if (!Config::inst()->get('Geocodable', 'is_geocodable')) {
            return;
        }
        if ($this->owner->LatLngOverride) {
            return;
        }
        if (!$this->owner->hasMethod('isAddressChanged') || !$this->owner->isAddressChanged()) {
            return;
        }

        $address = $this->owner->getFullAddress();
        $region = strtolower($this->owner->Country);

        $point = GoogleGeocoding::address_to_point($address, $region);
        if (!$point)
        {
            return;
        }

        $this->owner->Lat = $point['lat'];
        $this->owner->Lng = $point['lng'];
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(array('LatLngOverride', 'Lat', 'Lng'));

        // Adds Lat/Lng fields for viewing in the CMS
        $compositeField = CompositeField::create();
        $compositeField->push($overrideField = CheckboxField::create('LatLngOverride', 'Override Latitude and Longitude?'));
        $overrideField->setDescription('Check this box and save to be able to edit the latitude and longitude manually.');
        if ($this->owner->Lng && $this->owner->Lat) {
            $googleMapURL = 'http://maps.google.com/?q='.$this->owner->Lat.','.$this->owner->Lng;
            $googleMapDiv = '<div class="field"><label class="left" for="Form_EditForm_MapURL_Readonly">Google Map</label><div class="middleColumn"><a href="'.$googleMapURL.'" target="_blank">'.$googleMapURL.'</a></div></div>';
            $compositeField->push(LiteralField::create('MapURL_Readonly', $googleMapDiv));
        }
        if ($this->owner->LatLngOverride) {
            $compositeField->push(TextField::create('Lat', 'Lat'));
            $compositeField->push(TextField::create('Lng', 'Lng'));
        } else {
            $compositeField->push(ReadonlyField::create('Lat_Readonly', 'Lat', $this->owner->Lat));
            $compositeField->push(ReadonlyField::create('Lng_Readonly', 'Lng', $this->owner->Lng));
        }
        if ($this->owner->hasExtension('Addressable')) {
            // If using addressable, put the fields with it
            $fields->addFieldToTab('Root.Address', ToggleCompositeField::create('Coordinates', 'Coordinates', $compositeField));
        } else if ($this->owner instanceof SiteTree) {
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

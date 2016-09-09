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
        'Lat' => 'Decimal(10,7)',
        'Lng' => 'Decimal(10,7)'
    );

    public function onBeforeWrite()
    {
        if (!$this->owner->isAddressChanged())
            return;
        /*
         * Enable/Disable Geocode mapping
         */
        $isGeocodable = Config::inst()->get('Geocodable', 'is_geocodable');
        if ($isGeocodable)
        {
            $address = $this->owner->getFullAddress();
            $region = strtolower($this->owner->Country);

            if (!$point = GoogleGeocoding::address_to_point($address, $region))
            {
                return;
            }

            $this->owner->Lat = $point['lat'];
            $this->owner->Lng = $point['lng'];
        }
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(array('Lat', 'Lng'));

        // Adds Lat/Lng fields for viewing in the CMS
        $compositeField = CompositeField::create();
        if ($this->owner->Lng && $this->owner->Lat) {
            $googleMapURL = 'http://maps.google.com/?q='.$this->owner->Lat.','.$this->owner->Lng;
            $googleMapDiv = '<div class="field"><label class="left" for="Form_EditForm_MapURL_Readonly">Google Map</label><div class="middleColumn"><a href="'.$googleMapURL.'" target="_blank">'.$googleMapURL.'</a></div></div>';
            $compositeField->push(LiteralField::create('MapURL_Readonly', $googleMapDiv));
        }
        $compositeField->push(ReadonlyField::create('Lat_Readonly', 'Lat', $this->owner->Lat));
        $compositeField->push(ReadonlyField::create('Lng_Readonly', 'Lng', $this->owner->Lng));
        if ($this->owner->hasExtension('Addressable')) {
            // If using addressable, put the fields with it
            $fields->addFieldToTab('Root.Address', ToggleCompositeField::create('Coordinates', 'Coordinates', $compositeField));
        } else if ($this->owner instanceof SiteTree) {
            // If SIteTree but not using Addressable, put in 'Metadata' toggle composite field
            $fields->insertAfter($compositeField, 'ExtraMeta');
        } else {
            $fields->addFieldToTab('Root.Main', ToggleCompositeField::create('Coordinates', 'Coordinates', $compositeField));
        }
    }

    public function updateFrontEndFields(FieldList $fields)
    {
        $this->updateCMSFields($fields);
    }

}

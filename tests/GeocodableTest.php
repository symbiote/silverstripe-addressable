<?php

namespace Symbiote\Addressable\Tests;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use Symbiote\Addressable\Geocodable;
use Symbiote\Addressable\GoogleGeocodeService;

class GeocodableTest extends SapphireTest
{
    protected static $use_draft_site = true;

    protected $usesDatabase = true;

    /**
     * Test retrival of Lat/Lng from Google Maps endpoint (or similar)
     */
    public function testUpdatingLatLngFromAddress()
    {
        $record = new GeocodableDataObjectTest();
        $record->Address = '101-103 Courtenay Place';
        $record->Suburb = 'Wellington';
        $record->Postcode = '6011';
        $record->Country = 'NZ';
        $record->write();

        $expected = [
            'lat' => -41.2926516,
            'lng' => 174.7789566
        ];

        $e = $record->getLastGeocodableException();
        if ($e &&
            $e->getStatus() === GoogleGeocodeService::ERROR_OVER_QUERY_LIMIT) {
            $this->markTestSkipped(
                'Skipping '. get_class($this).'::'.__FUNCTION__.'() due to being over quota limit. Exception: '.$e->getMessage()
            );
            $this->skipTest = true;
            return;
        }

        $this->assertEquals(
            $expected,
            ['lat' => $record->Lat, 'lng' => $record->Lng]
        );
    }

    /**
     * Get the last geocodable error
     */
    public function testGetLastError()
    {
        $record = new GeocodableDataObjectTest();
        $record->Address = '33 Jeremy McDooglestrontles House';
        $record->Suburb = 'Frinkiac';
        $record->Postcode = '3011';
        $record->Country = '';
        $record->write();

        $e = $record->getLastGeocodableException();
        if ($e &&
            $e->getStatus() === GoogleGeocodeService::ERROR_OVER_QUERY_LIMIT) {
            $this->markTestSkipped(
                'Skipping '. get_class($this).'::'.__FUNCTION__.'() due to being over quota limit. Exception: '.$e->getMessage()
            );
            $this->skipTest = true;
            return;
        }

        $this->assertEquals(
            GoogleGeocodeService::ERROR_ZERO_RESULTS,
            $e->getStatus()
        );
    }

    /**
     * Make sure that Lat / Lng is not written to if "is_geocodable"
     * is false.
     */
    public function testDisableLatLngUpdate()
    {
        Config::inst()->update(Geocodable::class, 'is_geocodable', false);

        $record = new GeocodableDataObjectTest();
        $record->Address = '101-103 Courtenay Place';
        $record->Suburb = 'Wellington';
        $record->Postcode = '6011';
        $record->Country = 'NZ';
        $record->write();

        $expected = [
            'lat' => 0,
            'lng' => 0,
        ];
        $this->assertEquals(
            $expected,
            ['lat' => $record->Lat, 'lng' => $record->Lng]
        );
    }

    /**
     * Test case for when a CMS user wants to override a Lat/Lng value
     * and not automatically retrieve from based on the address information.
     */
    public function testLatLngOverride()
    {
        $record = new GeocodableDataObjectTest();
        $record->Address = '101-103 Courtenay Place';
        $record->Suburb = 'Wellington';
        $record->Postcode = '6011';
        $record->Country = 'NZ';

        $record->LatLngOverride = true;
        $record->Lat = -37.8182805;
        $record->Lng = 144.9505869;
        $record->write();

        $expected = [
            'lat' => -37.8182805,
            'lng' => 144.9505869,
        ];
        $this->assertEquals(
            $expected,
            ['lat' => $record->Lat, 'lng' => $record->Lng]
        );
    }

     /**
      * When using Google Maps to retrieve a Lat/Lng, it only gives you back
      * up to 7 decimal places, so we limited the Geocodable fields to `Decimal(10,7)`
      */
    public function testIntendedTruncation()
    {
        $record = new GeocodableDataObjectTest();
        $record->Address = '101-103 Courtenay Place';
        $record->Suburb = 'Wellington';
        $record->Postcode = '6011';
        $record->Country = 'NZ';

        $record->LatLngOverride = true;
        $record->Lat = -35.8182805316702;
        $record->Lng = 142.9505869187165;
        $record->write();

        // NOTE(Jake): 2018-07-25
        //
        // We re-retrieve the value as truncation only occurs when
        // a record is re-fetched from the database.
        //
        $record = GeocodableDataObjectTest::get()->byID($record->ID);

        $expected = [
           'lat' => -35.8182805,
           'lng' => 142.9505869,
        ];
        $this->assertEquals(
            $expected,
            ['lat' => $record->Lat, 'lng' => $record->Lng]
        );
    }
}

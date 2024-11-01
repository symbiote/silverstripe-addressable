<?php

namespace Symbiote\Addressable\Tests;

use Symbiote\Addressable\Addressable;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\i18n\Data\Intl\IntlLocales;

class AddressableTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testAddressableWrite()
    {
        $silverStripe = new AddressableDataObjectTest();
        $silverStripe->Address = '101-103 Courtenay Place';
        $silverStripe->Suburb = 'Wellington';
        $silverStripe->Postcode = '6011';
        $silverStripe->Country = 'NZ';
        $silverStripe->write();
        $silverStripeID = $silverStripe->ID;

        $this->assertTrue($silverStripeID > 0);

        $dynamic = new AddressableDataObjectTest();
        $dynamic->Address = '1526 South 12th Street';
        $dynamic->Suburb = 'Sheboygan';
        $dynamic->State = 'WI';
        $dynamic->Postcode = '53081';
        $dynamic->Country = 'US';
        $dynamic->write();
        $dynamicID = $dynamic->ID;

        $this->assertTrue($dynamicID > 0);

        $addressable = AddressableDataObjectTest::get()->byID($silverStripeID);
        $addressable2 = AddressableDataObjectTest::get()->byID($dynamicID);


        $this->assertTrue($addressable->Address == '101-103 Courtenay Place');
        $this->assertTrue($addressable->Suburb == 'Wellington');
        $this->assertTrue($addressable->Postcode == '6011');
        $this->assertTrue($addressable->Country == 'NZ');

        $this->assertTrue($addressable2->Address == '1526 South 12th Street');
        $this->assertTrue($addressable2->Suburb == 'Sheboygan');
        $this->assertTrue($addressable2->State == 'WI');
        $this->assertTrue($addressable2->Postcode == '53081');
        $this->assertTrue($addressable2->Country == 'US');
    }

    /**
     * Test the case where nothing is configured for the allowed_countries so
     * we fallback to a full list of countries provided by SilverStripe.
     */
    public function testConfigureNoCountry()
    {
        $record = new AddressableDataObjectTest();

        // Test that nothing is populated by default
        // (we only populate if 1 item is defined in the list)
        $this->assertEquals(
            '',
            $record->Country
        );

        // Test that with nothing configured, it gets all countries
        $this->assertEquals(
            IntlLocales::singleton()->config()->get('countries'),
            $record->getAllowedCountries()
        );
    }

    /**
     * Test the case where we configure 1 country in the allowed_countries config.
     */
    public function testConfigureOneCountryGlobally()
    {
        Config::inst()->set(Addressable::class, 'allowed_countries', [
            'au' => 'Australia',
        ]);
        $record = new AddressableDataObjectTest();

        // Test that populateDefaults() is working
        $this->assertEquals(
            'au',
            $record->Country
        );

        // Test that we only get one country back in array
        $this->assertEquals(
            [
                'au' => 'Australia',
            ],
            $record->getAllowedCountries()
        );
    }

    public function testConfigureOneCountryOnExtendable()
    {
        Config::inst()->set(AddressableDataObjectTest::class, 'allowed_countries', [
            'nz' => 'New Zealand',
        ]);
        $record = new AddressableDataObjectTest();

        // Test that populateDefaults() is working
        $this->assertEquals(
            'nz',
            $record->Country
        );

        // Test that we only get one country back in array
        $this->assertEquals(
            [
                'nz' => 'New Zealand',
            ],
            $record->getAllowedCountries()
        );
    }

    public function testConfigureOneStateGlobally()
    {
        Config::inst()->set(Addressable::class, 'allowed_states', [
            'vic' => 'Victoria',
        ]);
        $record = new AddressableDataObjectTest();

        // Test that populateDefaults() is working
        $this->assertEquals(
            'vic',
            $record->State
        );

        // Test that we only get one country back in array
        $this->assertEquals(
            [
                'vic' => 'Victoria',
            ],
            $record->getAllowedStates()
        );
    }

    public function testConfigureOneStateOnExtendable()
    {
        Config::inst()->set(AddressableDataObjectTest::class, 'allowed_states', [
            'nsw' => 'New South Wales',
        ]);
        $record = new AddressableDataObjectTest();

        // Test that populateDefaults() is working
        $this->assertEquals(
            'nsw',
            $record->State
        );

        // Test that we only get one country back in array
        $this->assertEquals(
            [
                'nsw' => 'New South Wales',
            ],
            $record->getAllowedStates()
        );
    }
}

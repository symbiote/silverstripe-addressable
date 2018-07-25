<?php

namespace Symbiote\Addressable\Tests;

use SilverStripe\Dev\SapphireTest;

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
}

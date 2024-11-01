<?php

namespace Symbiote\Addressable\Tests;

use Symbiote\Addressable\GeocodeService;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

class AddressableTemplateTest extends SapphireTest
{
    /**
     * This is a mock key that resembles a real Google Map API key
     */
    public const FAKE_GOOGLE_MAP_API_KEY = 'BIaaSyVtig225B4-a8O4Y_BVHl5Bp9ATkkBZgVp';

    public function testAddressMap()
    {
        // NOTE(Jake):2018-07-27
        //
        // This is **not** a real key.
        // This is to test that the key gets put into the embeddable map properly.
        //
        Config::inst()->set(GeocodeService::class, 'google_api_key', self::FAKE_GOOGLE_MAP_API_KEY);

        $record = new AddressableDataObjectTest();
        $record->Address = '101-103 Courtenay Place';
        $record->Suburb = 'Wellington';
        $record->Postcode = '6011';
        $record->Country = 'NZ';

        $expected = <<<HTML
    <div class="addressMap">
        <a href="https://maps.google.com/?q=101-103%20Courtenay%20Place%2C%20Wellington%2C%206011%2C%20New%20Zealand">
            <img
                src="https://maps.googleapis.com/maps/api/staticmap?size=320x240&scale=1&markers=101-103%20Courtenay%20Place%2C%20Wellington%2C%206011%2C%20New%20Zealand&key=BIaaSyVtig225B4-a8O4Y_BVHl5Bp9ATkkBZgVp"
                alt="101-103CourtenayPlace,Wellington,6011,NewZealand"
            />
        </a>
    </div>
HTML;
        $this->assertEqualIgnoringWhitespace(
            $expected,
            $record->AddressMap()->getValue()
        );
    }

    /**
     * Taken from "framework\tests\view\SSViewerTest.php"
     */
    protected function assertEqualIgnoringWhitespace($a, $b, $message = '')
    {
        $this->assertEquals(preg_replace('/\s+/', '', $a), preg_replace('/\s+/', '', $b), $message);
    }
}

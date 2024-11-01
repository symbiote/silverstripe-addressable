<?php

namespace Symbiote\Addressable\Tests;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\SapphireTest;
use Symbiote\Addressable\GoogleGeocodeService;

class GeocodableApiValuesTest extends SapphireTest
{

    protected $envApiKey = 'env-key';

    protected $googleGeocodeServiceApiKey = 'google-geocode-service-key';

    protected $geocodeServiceApiKey = 'geocode-service-key';

    protected $googleGeocodeServiceApiUrl = 'https://example.com/googleGeocodeServiceApiUrl';

    protected $geocodeServiceApiUrl = 'https://example.com/geocodeServiceApiUrl';

    /**
     * Set up config
     */
    public function setUp() : void
    {
        parent::setUp();
        Environment::setEnv('GOOGLE_API_KEY', $this->envApiKey);
        Config::inst()->set(GoogleGeocodeService::class, 'google_api_key', $this->googleGeocodeServiceApiKey);
        Config::inst()->set('Symbiote\\Addressable\\GeocodeService', 'google_api_key', $this->geocodeServiceApiKey);
        Config::inst()->set(GoogleGeocodeService::class, 'google_api_url', $this->googleGeocodeServiceApiUrl);
        Config::inst()->set('Symbiote\\Addressable\\GeocodeService', 'google_api_url', $this->geocodeServiceApiUrl);
    }

    /**
     * Reset config
     */
    public function tearDown() : void
    {
        parent::tearDown();
        Environment::setEnv('GOOGLE_API_KEY', false);
        Config::inst()->set(GoogleGeocodeService::class, 'google_api_key', null);
        Config::inst()->set('Symbiote\\Addressable\\GeocodeService', 'google_api_key', null);
        Config::inst()->set(GoogleGeocodeService::class, 'google_api_url', null);
        Config::inst()->set('Symbiote\\Addressable\\GeocodeService', 'google_api_url', null);
    }

    /**
     * Test setting and getting API key, including legacy config
     */
    public function testApiKeySetGet()
    {
        $service = new GoogleGeocodeService();

        $key = $service->getApiKey();
        $this->assertEquals($this->envApiKey, $key, "The API key should be the key set from environment");

        // false environment value
        Environment::setEnv('GOOGLE_API_KEY', false);
        $key = $service->getApiKey();
        $this->assertEquals($this->googleGeocodeServiceApiKey, $key, "The API key should be the key set via config API on GoogleGeocodeService");

        // null environment value
        Environment::setEnv('GOOGLE_API_KEY', null);
        $key = $service->getApiKey();
        $this->assertEquals($this->googleGeocodeServiceApiKey, $key, "The API key should be the key set via config API on GoogleGeocodeService");

        // empty string value
        Environment::setEnv('GOOGLE_API_KEY', false);
        Config::inst()->set(GoogleGeocodeService::class, 'google_api_key', '');
        $key = $service->getApiKey();
        $this->assertEquals($this->geocodeServiceApiKey, $key, "The API key should be the key set via config API on legacy GeocodeService");

        // null value
        Environment::setEnv('GOOGLE_API_KEY', false);
        Config::inst()->set(GoogleGeocodeService::class, 'google_api_key', null);
        $key = $service->getApiKey();
        $this->assertEquals($this->geocodeServiceApiKey, $key, "The API key should be the key set via config API on legacy GeocodeService");

        // lack of config value should throw an \Exception
        Environment::setEnv('GOOGLE_API_KEY', false);
        Config::inst()->set(GoogleGeocodeService::class, 'google_api_key', null);
        Config::inst()->set('Symbiote\\Addressable\\GeocodeService', 'google_api_key', null);
        try {
            $key = $service->getApiKey();
            $this->assertTrue(false, "Lack of an API key should trigger an \Exception");
        } catch (\Exception) {
            // noop
        }
    }

    /**
     * Test setting and getting API URL, including legacy config
     */
    public function testApiUrlSetGet()
    {
        $service = new GoogleGeocodeService();

        $url = $service->getApiUrl();
        $this->assertEquals($this->googleGeocodeServiceApiUrl, $url, "The API URL should be the URL set via config API on GoogleGeocodeService");

        // empty string value
        Config::inst()->set(GoogleGeocodeService::class, 'google_api_url', '');
        $url = $service->getApiUrl();
        $this->assertEquals($this->geocodeServiceApiUrl, $url, "The API url should be the url set via config API on legacy GeocodeService");

        // null value
        Config::inst()->set(GoogleGeocodeService::class, 'google_api_url', null);
        $url = $service->getApiUrl();
        $this->assertEquals($this->geocodeServiceApiUrl, $url, "The API url should be the url set via config API on legacy GeocodeService");

        // lack of config value should throw an \Exception
        Config::inst()->set(GoogleGeocodeService::class, 'google_api_url', null);
        Config::inst()->set('Symbiote\\Addressable\\GeocodeService', 'google_api_url', null);
        try {
            $url = $service->getApiUrl();
            $this->assertTrue(false, "Lack of an API URL should trigger an \Exception");
        } catch (\Exception) {
            // noop
        }
    }
}

<?php

namespace Symbiote\Addressable;

use SilverStripe\Core\Config\Config;
use GuzzleHttp\Client;
use Exception;

/**
 * A utility class for geocoding addresses using the mapbox maps API.
 *
 * @package silverstripe-addressable
 */
class MapboxGeocodeService implements GeocodeServiceInterface
{

    /**
     * @var string
     * @config
     */
    private static $mapbox_api_url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/';

    /**
     * @var string
     * @config
     */
    private static $mapbox_api_key = '';

    /**
     * Convert an address into a latitude and longitude.
     *
     * @param string $address The address to geocode.
     * @param string $region  An optional two letter region code.
     * @return array An associative array with lat and lng keys.
     */
    public function addressToPoint($address, $region = '')
    {
        // Get the URL for the Mapbox API
        $url = Config::inst()->get(__CLASS__, 'mapbox_api_url');
        $key = Config::inst()->get(__CLASS__, 'mapbox_api_key');

        if (!$url) {
            // If no URL configured. Stop.
            throw new Exception('No mapbox_api_url configured. This is not allowed.');
        }

        if (!$key) {
            // If no KEY configured. Stop.
            throw new Exception('No mapbox_api_key configured. This is not allowed.');
        }

        // Add params
        $queryVars = [
            'access_token' => $key,
            'type' => 'address',
        ];
        if ($region) {
            $queryVars['country'] = $region;
        }
        $url .= urlencode($address).'.json?'.http_build_query($queryVars);

        $client = new Client();
        $response = $client->get($url);
        if (!$response) {
            throw new GeocodeServiceException('No response.', 0, '');
        }
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new GeocodeServiceException('Unexpected status code:'.$statusCode, $statusCode, '');
        }
        $responseBody = json_decode((string)$response->getBody());

        // Error handling
        if ($responseBody) {
            if (isset($responseBody->message)) {
                throw new GeocodeServiceException('Error message: '.$responseBody->message, $statusCode, $responseBody);
            }
            if (!isset($responseBody->features) || !count($responseBody->features) === 0) {
                throw new GeocodeServiceException('Zero results returned. Invalid status from response: '.$status, $statusCode, $responseBody);
            }
        } else {
            // Fallback to full string dump
            $text = trim($response->getBody());
            throw new GeocodeServiceException('Invalid response: '.$text, $statusCode, $responseBody);
        }

        // We take the first match, because that's most likely the best match
        $feature = reset($responseBody->features);
        list($lng, $lat) = $feature->geometry->coordinates;

        return [
            'lat' => (float)$lat,
            'lng' => (float)$lng
        ];
    }
}

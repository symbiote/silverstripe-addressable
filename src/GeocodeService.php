<?php

namespace Symbiote\Addressable;

use SilverStripe\Core\Config\Config;
use SilverStripe\Control\Director;
use SimpleXMLElement;
use Exception;

/**
 * A utility class for geocoding addresses using the google maps API.
 *
 * @package silverstripe-addressable
 */
class GeocodeService
{
    const ERROR_ZERO_RESULTS = 'ZERO_RESULTS';

    const ERROR_OVER_QUERY_LIMIT = 'OVER_QUERY_LIMIT';

    /**
     * @var string
     * @config
     */
    private static $google_api_url = 'https://maps.googleapis.com/maps/api/geocode/xml';

    /**
     * @var string
     * @config
     */
    private static $google_api_key = '';

    /**
     * Convert an address into a latitude and longitude.
     *
     * @param string $address The address to geocode.
     * @param string $region  An optional two letter region code.
     * @return array An associative array with lat and lng keys.
     */
    public function addressToPoint($address, $region = '')
    {
        // Get the URL for the Google API
        $url = Config::inst()->get(__CLASS__, 'google_api_url');
        $key = Config::inst()->get(__CLASS__, 'google_api_key');

        if (!$url) {
            // If no URL configured. Stop.
            throw new GeocodeServiceException('No google_api_url configured. This is not allowed.');
        }

        // Add params
        $queryVars = [
            'address' => $address,
            'sensor'  => 'false',
        ];
        if ($region) {
            $queryVars['region'] = $region;
        }
        if ($key) {
            $queryVars['key'] = $key;
        }
        $url .= '?'.http_build_query($queryVars);

        $client = new \GuzzleHttp\Client();
        $response = $client->get($url);
        if (!$response) {
            throw new GeocodeServiceException('No response.', 0, '');
        }
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            throw new GeocodeServiceException('Unexpected status code:'.$statusCode, $statusCode, '');
        }
        $responseBody = (string)$response->getBody();
        $xml = new SimpleXMLElement($responseBody);
        if (!isset($xml->result)) {
            // Error handling
            if (isset($xml->status)) {
                $status = (string)$xml->status;
                if ($status === self::ERROR_ZERO_RESULTS) {
                    throw new GeocodeServiceException('Zero results returned. Invalid status from response: '.$status, $statusCode, $responseBody);
                } else {
                    throw new GeocodeServiceException('Unhandled status from response: '.$status, $statusCode, $responseBody);
                }
            }
            // Fallback to full string dump
            $text = trim($response->getBody());
            throw new GeocodeServiceException('Invalid response: '.$text, $responseBody);
        }
        $location = $xml->result->geometry->location;
        return [
            'lat' => (float)$location->lat,
            'lng' => (float)$location->lng
        ];
    }
}

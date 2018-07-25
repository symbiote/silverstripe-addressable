<?php
/**
 * A utility class for geocoding addresses using the google maps API.
 *
 * @package silverstripe-addressable
 */
class GoogleGeocoding
{

    /**
     * Convert an address into a latitude and longitude.
     *
     * @param string $address The address to geocode.
     * @param string $region  An optional two letter region code.
     * @return array An associative array with lat and lng keys.
     */
    public static function address_to_point($address, $region = null)
    {
        // Get the URL for the Google API
        $url = Config::inst()->get('GoogleGeocoding', 'google_api_url');
        $key = Config::inst()->get('GoogleGeocoding', 'google_api_key');

        // Query the Google API
        $service = new RestfulService($url);
        $service->setQueryString(array(
            'address' => $address,
            'sensor'  => 'false',
            'region'  => $region,
            'key'       => $key
        ));
        if ($service->request()->getStatusCode() === 500) {
            $errorMessage = '500 status code, Are you sure your SSL certificates are properly setup? You can workaround this locally by setting CURLOPT_SSL_VERIFYPEER to "false", however this is not recommended for security reasons.';
            if (Director::isDev()) {
                throw new Exception($errorMessage);
            } else {
                user_error($errorMessage, E_USER_WARNING);
            }
            return false;
        }
        if ($service->request()->getStatusCode() !== 200) {
            $errorMessage = $service->request()->getBody();
            if (Director::isDev()) {
                throw new Exception($errorMessage);
            } else {
                user_error($errorMessage, E_USER_WARNING);
            }
            return false;
        }
        if (!$service->request()->getBody()) {
            // If blank response, ignore to avoid XML parsing errors.
            return false;
        }
        $response = $service->request()->simpleXML();

        if ($response->status != 'OK') {
            return false;
        }

        $location = $response->result->geometry->location;
        return array(
            'lat' => (float) $location->lat,
            'lng' => (float) $location->lng
        );
    }
}

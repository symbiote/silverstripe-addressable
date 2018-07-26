<?php

namespace Symbiote\Addressable;

use Exception;
use SimpleXMLElement;

/**
 * @package silverstripe-addressable
 */
class GeocodeServiceException extends Exception
{
    /**
     * @var string
     */
    private $responseBody;

    /**
     * @var string OK, ZERO_RESULTS, OVER_QUERY_LIMIT
     */
    private $status;

    public function __construct($message, $statusCode, $responseBody, Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $responseBody = (string)$responseBody;
        $this->responseBody = $responseBody;

        $xml = new SimpleXMLElement($responseBody);
        if (isset($xml->status)) {
            $this->status = (string)$xml->status;
        }
    }

    public function getResponse()
    {
        return $this->responseBody;
    }

    /**
     * Return "status" values:
     * - ZERO_RESULTS
     * - OVER_QUERY_LIMIT
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}

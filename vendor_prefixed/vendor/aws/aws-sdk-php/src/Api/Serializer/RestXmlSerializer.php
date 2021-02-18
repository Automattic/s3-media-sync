<?php

namespace WPCOM_VIP\Aws\Api\Serializer;

use WPCOM_VIP\Aws\Api\StructureShape;
use WPCOM_VIP\Aws\Api\Service;
/**
 * @internal
 */
class RestXmlSerializer extends \WPCOM_VIP\Aws\Api\Serializer\RestSerializer
{
    /** @var XmlBody */
    private $xmlBody;
    /**
     * @param Service $api      Service API description
     * @param string  $endpoint Endpoint to connect to
     * @param XmlBody $xmlBody  Optional XML formatter to use
     */
    public function __construct(\WPCOM_VIP\Aws\Api\Service $api, $endpoint, \WPCOM_VIP\Aws\Api\Serializer\XmlBody $xmlBody = null)
    {
        parent::__construct($api, $endpoint);
        $this->xmlBody = $xmlBody ?: new \WPCOM_VIP\Aws\Api\Serializer\XmlBody($api);
    }
    protected function payload(\WPCOM_VIP\Aws\Api\StructureShape $member, array $value, array &$opts)
    {
        $opts['headers']['Content-Type'] = 'application/xml';
        $opts['body'] = (string) $this->xmlBody->build($member, $value);
    }
}

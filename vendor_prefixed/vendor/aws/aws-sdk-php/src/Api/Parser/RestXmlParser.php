<?php

namespace WPCOM_VIP\Aws\Api\Parser;

use WPCOM_VIP\Aws\Api\StructureShape;
use WPCOM_VIP\Aws\Api\Service;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
use WPCOM_VIP\Psr\Http\Message\StreamInterface;
/**
 * @internal Implements REST-XML parsing (e.g., S3, CloudFront, etc...)
 */
class RestXmlParser extends \WPCOM_VIP\Aws\Api\Parser\AbstractRestParser
{
    use PayloadParserTrait;
    /**
     * @param Service   $api    Service description
     * @param XmlParser $parser XML body parser
     */
    public function __construct(\WPCOM_VIP\Aws\Api\Service $api, \WPCOM_VIP\Aws\Api\Parser\XmlParser $parser = null)
    {
        parent::__construct($api);
        $this->parser = $parser ?: new \WPCOM_VIP\Aws\Api\Parser\XmlParser();
    }
    protected function payload(\WPCOM_VIP\Psr\Http\Message\ResponseInterface $response, \WPCOM_VIP\Aws\Api\StructureShape $member, array &$result)
    {
        $result += $this->parseMemberFromStream($response->getBody(), $member, $response);
    }
    public function parseMemberFromStream(\WPCOM_VIP\Psr\Http\Message\StreamInterface $stream, \WPCOM_VIP\Aws\Api\StructureShape $member, $response)
    {
        $xml = $this->parseXml($stream, $response);
        return $this->parser->parse($member, $xml);
    }
}

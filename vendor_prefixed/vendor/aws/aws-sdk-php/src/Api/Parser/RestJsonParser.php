<?php

namespace WPCOM_VIP\Aws\Api\Parser;

use WPCOM_VIP\Aws\Api\Service;
use WPCOM_VIP\Aws\Api\StructureShape;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
use WPCOM_VIP\Psr\Http\Message\StreamInterface;
/**
 * @internal Implements REST-JSON parsing (e.g., Glacier, Elastic Transcoder)
 */
class RestJsonParser extends \WPCOM_VIP\Aws\Api\Parser\AbstractRestParser
{
    use PayloadParserTrait;
    /**
     * @param Service    $api    Service description
     * @param JsonParser $parser JSON body builder
     */
    public function __construct(\WPCOM_VIP\Aws\Api\Service $api, \WPCOM_VIP\Aws\Api\Parser\JsonParser $parser = null)
    {
        parent::__construct($api);
        $this->parser = $parser ?: new \WPCOM_VIP\Aws\Api\Parser\JsonParser();
    }
    protected function payload(\WPCOM_VIP\Psr\Http\Message\ResponseInterface $response, \WPCOM_VIP\Aws\Api\StructureShape $member, array &$result)
    {
        $jsonBody = $this->parseJson($response->getBody(), $response);
        if ($jsonBody) {
            $result += $this->parser->parse($member, $jsonBody);
        }
    }
    public function parseMemberFromStream(\WPCOM_VIP\Psr\Http\Message\StreamInterface $stream, \WPCOM_VIP\Aws\Api\StructureShape $member, $response)
    {
        $jsonBody = $this->parseJson($stream, $response);
        if ($jsonBody) {
            return $this->parser->parse($member, $jsonBody);
        }
        return [];
    }
}

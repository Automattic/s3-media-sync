<?php

namespace WPCOM_VIP\Aws\Api\Parser;

use WPCOM_VIP\Aws\Api\StructureShape;
use WPCOM_VIP\Aws\Api\Service;
use WPCOM_VIP\Aws\Result;
use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
use WPCOM_VIP\Psr\Http\Message\StreamInterface;
/**
 * @internal Implements JSON-RPC parsing (e.g., DynamoDB)
 */
class JsonRpcParser extends \WPCOM_VIP\Aws\Api\Parser\AbstractParser
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
    public function __invoke(\WPCOM_VIP\Aws\CommandInterface $command, \WPCOM_VIP\Psr\Http\Message\ResponseInterface $response)
    {
        $operation = $this->api->getOperation($command->getName());
        $result = null === $operation['output'] ? null : $this->parseMemberFromStream($response->getBody(), $operation->getOutput(), $response);
        return new \WPCOM_VIP\Aws\Result($result ?: []);
    }
    public function parseMemberFromStream(\WPCOM_VIP\Psr\Http\Message\StreamInterface $stream, \WPCOM_VIP\Aws\Api\StructureShape $member, $response)
    {
        return $this->parser->parse($member, $this->parseJson($stream, $response));
    }
}

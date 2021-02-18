<?php

namespace WPCOM_VIP\Aws\Api\ErrorParser;

use WPCOM_VIP\Aws\Api\Parser\JsonParser;
use WPCOM_VIP\Aws\Api\Service;
use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
/**
 * Parsers JSON-RPC errors.
 */
class JsonRpcErrorParser extends \WPCOM_VIP\Aws\Api\ErrorParser\AbstractErrorParser
{
    use JsonParserTrait;
    private $parser;
    public function __construct(\WPCOM_VIP\Aws\Api\Service $api = null, \WPCOM_VIP\Aws\Api\Parser\JsonParser $parser = null)
    {
        parent::__construct($api);
        $this->parser = $parser ?: new \WPCOM_VIP\Aws\Api\Parser\JsonParser();
    }
    public function __invoke(\WPCOM_VIP\Psr\Http\Message\ResponseInterface $response, \WPCOM_VIP\Aws\CommandInterface $command = null)
    {
        $data = $this->genericHandler($response);
        // Make the casing consistent across services.
        if ($data['parsed']) {
            $data['parsed'] = \array_change_key_case($data['parsed']);
        }
        if (isset($data['parsed']['__type'])) {
            $parts = \explode('#', $data['parsed']['__type']);
            $data['code'] = isset($parts[1]) ? $parts[1] : $parts[0];
            $data['message'] = isset($data['parsed']['message']) ? $data['parsed']['message'] : null;
        }
        $this->populateShape($data, $response, $command);
        return $data;
    }
}

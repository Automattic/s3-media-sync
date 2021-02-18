<?php

namespace WPCOM_VIP\Aws\Api\Serializer;

use WPCOM_VIP\Aws\Api\Service;
use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\GuzzleHttp\Psr7\Request;
use WPCOM_VIP\Psr\Http\Message\RequestInterface;
/**
 * Serializes a query protocol request.
 * @internal
 */
class QuerySerializer
{
    private $endpoint;
    private $api;
    private $paramBuilder;
    public function __construct(\WPCOM_VIP\Aws\Api\Service $api, $endpoint, callable $paramBuilder = null)
    {
        $this->api = $api;
        $this->endpoint = $endpoint;
        $this->paramBuilder = $paramBuilder ?: new \WPCOM_VIP\Aws\Api\Serializer\QueryParamBuilder();
    }
    /**
     * When invoked with an AWS command, returns a serialization array
     * containing "method", "uri", "headers", and "body" key value pairs.
     *
     * @param CommandInterface $command
     *
     * @return RequestInterface
     */
    public function __invoke(\WPCOM_VIP\Aws\CommandInterface $command)
    {
        $operation = $this->api->getOperation($command->getName());
        $body = ['Action' => $command->getName(), 'Version' => $this->api->getMetadata('apiVersion')];
        $params = $command->toArray();
        // Only build up the parameters when there are parameters to build
        if ($params) {
            $body += \call_user_func($this->paramBuilder, $operation->getInput(), $params);
        }
        $body = \http_build_query($body, null, '&', \PHP_QUERY_RFC3986);
        return new \WPCOM_VIP\GuzzleHttp\Psr7\Request('POST', $this->endpoint, ['Content-Length' => \strlen($body), 'Content-Type' => 'application/x-www-form-urlencoded'], $body);
    }
}

<?php

namespace WPCOM_VIP\Aws\Api\Serializer;

use WPCOM_VIP\Aws\Api\MapShape;
use WPCOM_VIP\Aws\Api\Service;
use WPCOM_VIP\Aws\Api\Operation;
use WPCOM_VIP\Aws\Api\Shape;
use WPCOM_VIP\Aws\Api\StructureShape;
use WPCOM_VIP\Aws\Api\TimestampShape;
use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\GuzzleHttp\Psr7;
use WPCOM_VIP\GuzzleHttp\Psr7\Uri;
use WPCOM_VIP\GuzzleHttp\Psr7\UriResolver;
use WPCOM_VIP\Psr\Http\Message\RequestInterface;
/**
 * Serializes HTTP locations like header, uri, payload, etc...
 * @internal
 */
abstract class RestSerializer
{
    /** @var Service */
    private $api;
    /** @var Psr7\Uri */
    private $endpoint;
    /**
     * @param Service $api      Service API description
     * @param string  $endpoint Endpoint to connect to
     */
    public function __construct(\WPCOM_VIP\Aws\Api\Service $api, $endpoint)
    {
        $this->api = $api;
        $this->endpoint = \WPCOM_VIP\GuzzleHttp\Psr7\uri_for($endpoint);
    }
    /**
     * @param CommandInterface $command Command to serialized
     *
     * @return RequestInterface
     */
    public function __invoke(\WPCOM_VIP\Aws\CommandInterface $command)
    {
        $operation = $this->api->getOperation($command->getName());
        $args = $command->toArray();
        $opts = $this->serialize($operation, $args);
        $uri = $this->buildEndpoint($operation, $args, $opts);
        return new \WPCOM_VIP\GuzzleHttp\Psr7\Request($operation['http']['method'], $uri, isset($opts['headers']) ? $opts['headers'] : [], isset($opts['body']) ? $opts['body'] : null);
    }
    /**
     * Modifies a hash of request options for a payload body.
     *
     * @param StructureShape   $member  Member to serialize
     * @param array            $value   Value to serialize
     * @param array            $opts    Request options to modify.
     */
    protected abstract function payload(\WPCOM_VIP\Aws\Api\StructureShape $member, array $value, array &$opts);
    private function serialize(\WPCOM_VIP\Aws\Api\Operation $operation, array $args)
    {
        $opts = [];
        $input = $operation->getInput();
        // Apply the payload trait if present
        if ($payload = $input['payload']) {
            $this->applyPayload($input, $payload, $args, $opts);
        }
        foreach ($args as $name => $value) {
            if ($input->hasMember($name)) {
                $member = $input->getMember($name);
                $location = $member['location'];
                if (!$payload && !$location) {
                    $bodyMembers[$name] = $value;
                } elseif ($location == 'header') {
                    $this->applyHeader($name, $member, $value, $opts);
                } elseif ($location == 'querystring') {
                    $this->applyQuery($name, $member, $value, $opts);
                } elseif ($location == 'headers') {
                    $this->applyHeaderMap($name, $member, $value, $opts);
                }
            }
        }
        if (isset($bodyMembers)) {
            $this->payload($operation->getInput(), $bodyMembers, $opts);
        }
        return $opts;
    }
    private function applyPayload(\WPCOM_VIP\Aws\Api\StructureShape $input, $name, array $args, array &$opts)
    {
        if (!isset($args[$name])) {
            return;
        }
        $m = $input->getMember($name);
        if ($m['streaming'] || ($m['type'] == 'string' || $m['type'] == 'blob')) {
            // Streaming bodies or payloads that are strings are
            // always just a stream of data.
            $opts['body'] = \WPCOM_VIP\GuzzleHttp\Psr7\stream_for($args[$name]);
            return;
        }
        $this->payload($m, $args[$name], $opts);
    }
    private function applyHeader($name, \WPCOM_VIP\Aws\Api\Shape $member, $value, array &$opts)
    {
        if ($member->getType() === 'timestamp') {
            $timestampFormat = !empty($member['timestampFormat']) ? $member['timestampFormat'] : 'rfc822';
            $value = \WPCOM_VIP\Aws\Api\TimestampShape::format($value, $timestampFormat);
        }
        if ($member['jsonvalue']) {
            $value = \json_encode($value);
            if (empty($value) && \JSON_ERROR_NONE !== \json_last_error()) {
                throw new \InvalidArgumentException('Unable to encode the provided value' . ' with \'json_encode\'. ' . \json_last_error_msg());
            }
            $value = \base64_encode($value);
        }
        $opts['headers'][$member['locationName'] ?: $name] = $value;
    }
    /**
     * Note: This is currently only present in the Amazon S3 model.
     */
    private function applyHeaderMap($name, \WPCOM_VIP\Aws\Api\Shape $member, array $value, array &$opts)
    {
        $prefix = $member['locationName'];
        foreach ($value as $k => $v) {
            $opts['headers'][$prefix . $k] = $v;
        }
    }
    private function applyQuery($name, \WPCOM_VIP\Aws\Api\Shape $member, $value, array &$opts)
    {
        if ($member instanceof \WPCOM_VIP\Aws\Api\MapShape) {
            $opts['query'] = isset($opts['query']) && \is_array($opts['query']) ? $opts['query'] + $value : $value;
        } elseif ($value !== null) {
            $type = $member->getType();
            if ($type === 'boolean') {
                $value = $value ? 'true' : 'false';
            } elseif ($type === 'timestamp') {
                $timestampFormat = !empty($member['timestampFormat']) ? $member['timestampFormat'] : 'iso8601';
                $value = \WPCOM_VIP\Aws\Api\TimestampShape::format($value, $timestampFormat);
            }
            $opts['query'][$member['locationName'] ?: $name] = $value;
        }
    }
    private function buildEndpoint(\WPCOM_VIP\Aws\Api\Operation $operation, array $args, array $opts)
    {
        $varspecs = [];
        // Create an associative array of varspecs used in expansions
        foreach ($operation->getInput()->getMembers() as $name => $member) {
            if ($member['location'] == 'uri') {
                $varspecs[$member['locationName'] ?: $name] = isset($args[$name]) ? $args[$name] : null;
            }
        }
        $relative = \preg_replace_callback('/\\{([^\\}]+)\\}/', function (array $matches) use($varspecs) {
            $isGreedy = \substr($matches[1], -1, 1) == '+';
            $k = $isGreedy ? \substr($matches[1], 0, -1) : $matches[1];
            if (!isset($varspecs[$k])) {
                return '';
            }
            if ($isGreedy) {
                return \str_replace('%2F', '/', \rawurlencode($varspecs[$k]));
            }
            return \rawurlencode($varspecs[$k]);
        }, $operation['http']['requestUri']);
        // Add the query string variables or appending to one if needed.
        if (!empty($opts['query'])) {
            $append = \WPCOM_VIP\GuzzleHttp\Psr7\build_query($opts['query']);
            $relative .= \strpos($relative, '?') ? "&{$append}" : "?{$append}";
        }
        // If endpoint has path, remove leading '/' to preserve URI resolution.
        $path = $this->endpoint->getPath();
        if ($path && $relative[0] === '/') {
            $relative = \substr($relative, 1);
        }
        // Expand path place holders using Amazon's slightly different URI
        // template syntax.
        return \WPCOM_VIP\GuzzleHttp\Psr7\UriResolver::resolve($this->endpoint, new \WPCOM_VIP\GuzzleHttp\Psr7\Uri($relative));
    }
}

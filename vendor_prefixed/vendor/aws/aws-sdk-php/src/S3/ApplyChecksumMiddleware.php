<?php

namespace WPCOM_VIP\Aws\S3;

use WPCOM_VIP\Aws\Api\ApiProvider;
use WPCOM_VIP\Aws\Api\Service;
use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\GuzzleHttp\Psr7;
use WPCOM_VIP\Psr\Http\Message\RequestInterface;
/**
 * Apply required or optional MD5s to requests before sending.
 *
 * IMPORTANT: This middleware must be added after the "build" step.
 *
 * @internal
 */
class ApplyChecksumMiddleware
{
    private static $sha256 = ['PutObject', 'UploadPart'];
    /** @var Service */
    private $api;
    private $nextHandler;
    /**
     * Create a middleware wrapper function.
     *
     * @param Service $api
     * @return callable
     */
    public static function wrap(\WPCOM_VIP\Aws\Api\Service $api)
    {
        return function (callable $handler) use($api) {
            return new self($handler, $api);
        };
    }
    public function __construct(callable $nextHandler, \WPCOM_VIP\Aws\Api\Service $api)
    {
        $this->api = $api;
        $this->nextHandler = $nextHandler;
    }
    public function __invoke(\WPCOM_VIP\Aws\CommandInterface $command, \WPCOM_VIP\Psr\Http\Message\RequestInterface $request)
    {
        $next = $this->nextHandler;
        $name = $command->getName();
        $body = $request->getBody();
        $op = $this->api->getOperation($command->getName());
        if (!empty($op['httpChecksumRequired']) && !$request->hasHeader('Content-MD5')) {
            // Set the content MD5 header for operations that require it.
            $request = $request->withHeader('Content-MD5', \base64_encode(\WPCOM_VIP\GuzzleHttp\Psr7\hash($body, 'md5', \true)));
        } elseif (\in_array($name, self::$sha256) && $command['ContentSHA256']) {
            // Set the content hash header if provided in the parameters.
            $request = $request->withHeader('X-Amz-Content-Sha256', $command['ContentSHA256']);
        }
        return $next($command, $request);
    }
}

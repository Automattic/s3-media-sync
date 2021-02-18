<?php

namespace WPCOM_VIP\Aws\S3;

use WPCOM_VIP\Aws\Api\Parser\AbstractParser;
use WPCOM_VIP\Aws\Api\Parser\Exception\ParserException;
use WPCOM_VIP\Aws\Api\StructureShape;
use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\Aws\Exception\AwsException;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
use WPCOM_VIP\Psr\Http\Message\StreamInterface;
/**
 * Converts errors returned with a status code of 200 to a retryable error type.
 *
 * @internal
 */
class AmbiguousSuccessParser extends \WPCOM_VIP\Aws\Api\Parser\AbstractParser
{
    private static $ambiguousSuccesses = ['UploadPart' => \true, 'UploadPartCopy' => \true, 'CopyObject' => \true, 'CompleteMultipartUpload' => \true];
    /** @var callable */
    private $errorParser;
    /** @var string */
    private $exceptionClass;
    public function __construct(callable $parser, callable $errorParser, $exceptionClass = \WPCOM_VIP\Aws\Exception\AwsException::class)
    {
        $this->parser = $parser;
        $this->errorParser = $errorParser;
        $this->exceptionClass = $exceptionClass;
    }
    public function __invoke(\WPCOM_VIP\Aws\CommandInterface $command, \WPCOM_VIP\Psr\Http\Message\ResponseInterface $response)
    {
        if (200 === $response->getStatusCode() && isset(self::$ambiguousSuccesses[$command->getName()])) {
            $errorParser = $this->errorParser;
            try {
                $parsed = $errorParser($response);
            } catch (\WPCOM_VIP\Aws\Api\Parser\Exception\ParserException $e) {
                $parsed = ['code' => 'ConnectionError', 'message' => "An error connecting to the service occurred" . " while performing the " . $command->getName() . " operation."];
            }
            if (isset($parsed['code']) && isset($parsed['message'])) {
                throw new $this->exceptionClass($parsed['message'], $command, ['connection_error' => \true]);
            }
        }
        $fn = $this->parser;
        return $fn($command, $response);
    }
    public function parseMemberFromStream(\WPCOM_VIP\Psr\Http\Message\StreamInterface $stream, \WPCOM_VIP\Aws\Api\StructureShape $member, $response)
    {
        return $this->parser->parseMemberFromStream($stream, $member, $response);
    }
}

<?php

namespace WPCOM_VIP\Aws\S3;

use WPCOM_VIP\Aws\Api\Parser\AbstractParser;
use WPCOM_VIP\Aws\Api\StructureShape;
use WPCOM_VIP\Aws\Api\Parser\Exception\ParserException;
use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\Aws\Exception\AwsException;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
use WPCOM_VIP\Psr\Http\Message\StreamInterface;
/**
 * Converts malformed responses to a retryable error type.
 *
 * @internal
 */
class RetryableMalformedResponseParser extends \WPCOM_VIP\Aws\Api\Parser\AbstractParser
{
    /** @var string */
    private $exceptionClass;
    public function __construct(callable $parser, $exceptionClass = \WPCOM_VIP\Aws\Exception\AwsException::class)
    {
        $this->parser = $parser;
        $this->exceptionClass = $exceptionClass;
    }
    public function __invoke(\WPCOM_VIP\Aws\CommandInterface $command, \WPCOM_VIP\Psr\Http\Message\ResponseInterface $response)
    {
        $fn = $this->parser;
        try {
            return $fn($command, $response);
        } catch (\WPCOM_VIP\Aws\Api\Parser\Exception\ParserException $e) {
            throw new $this->exceptionClass("Error parsing response for {$command->getName()}:" . " AWS parsing error: {$e->getMessage()}", $command, ['connection_error' => \true, 'exception' => $e], $e);
        }
    }
    public function parseMemberFromStream(\WPCOM_VIP\Psr\Http\Message\StreamInterface $stream, \WPCOM_VIP\Aws\Api\StructureShape $member, $response)
    {
        return $this->parser->parseMemberFromStream($stream, $member, $response);
    }
}

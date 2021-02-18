<?php

namespace WPCOM_VIP\Aws\Handler\GuzzleV5;

use WPCOM_VIP\GuzzleHttp\Stream\StreamDecoratorTrait;
use WPCOM_VIP\GuzzleHttp\Stream\StreamInterface as GuzzleStreamInterface;
use WPCOM_VIP\Psr\Http\Message\StreamInterface as Psr7StreamInterface;
/**
 * Adapts a PSR-7 Stream to a Guzzle 5 Stream.
 *
 * @codeCoverageIgnore
 */
class GuzzleStream implements \WPCOM_VIP\GuzzleHttp\Stream\StreamInterface
{
    use StreamDecoratorTrait;
    /** @var Psr7StreamInterface */
    private $stream;
    public function __construct(\WPCOM_VIP\Psr\Http\Message\StreamInterface $stream)
    {
        $this->stream = $stream;
    }
}

<?php

namespace WPCOM_VIP\Aws\Handler\GuzzleV5;

use WPCOM_VIP\GuzzleHttp\Stream\StreamDecoratorTrait;
use WPCOM_VIP\GuzzleHttp\Stream\StreamInterface as GuzzleStreamInterface;
use WPCOM_VIP\Psr\Http\Message\StreamInterface as Psr7StreamInterface;
/**
 * Adapts a Guzzle 5 Stream to a PSR-7 Stream.
 *
 * @codeCoverageIgnore
 */
class PsrStream implements \WPCOM_VIP\Psr\Http\Message\StreamInterface
{
    use StreamDecoratorTrait;
    /** @var GuzzleStreamInterface */
    private $stream;
    public function __construct(\WPCOM_VIP\GuzzleHttp\Stream\StreamInterface $stream)
    {
        $this->stream = $stream;
    }
    public function rewind()
    {
        $this->stream->seek(0);
    }
    public function getContents()
    {
        return $this->stream->getContents();
    }
}

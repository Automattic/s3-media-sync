<?php

namespace WPCOM_VIP\Aws\Api\Parser;

use WPCOM_VIP\Aws\Api\Service;
use WPCOM_VIP\Aws\Api\StructureShape;
use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\Aws\ResultInterface;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
use WPCOM_VIP\Psr\Http\Message\StreamInterface;
/**
 * @internal
 */
abstract class AbstractParser
{
    /** @var \Aws\Api\Service Representation of the service API*/
    protected $api;
    /** @var callable */
    protected $parser;
    /**
     * @param Service $api Service description.
     */
    public function __construct(\WPCOM_VIP\Aws\Api\Service $api)
    {
        $this->api = $api;
    }
    /**
     * @param CommandInterface  $command  Command that was executed.
     * @param ResponseInterface $response Response that was received.
     *
     * @return ResultInterface
     */
    public abstract function __invoke(\WPCOM_VIP\Aws\CommandInterface $command, \WPCOM_VIP\Psr\Http\Message\ResponseInterface $response);
    public abstract function parseMemberFromStream(\WPCOM_VIP\Psr\Http\Message\StreamInterface $stream, \WPCOM_VIP\Aws\Api\StructureShape $member, $response);
}

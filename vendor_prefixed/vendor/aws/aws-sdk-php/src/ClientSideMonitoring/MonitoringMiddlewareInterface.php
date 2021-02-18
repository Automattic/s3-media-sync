<?php

namespace WPCOM_VIP\Aws\ClientSideMonitoring;

use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\Aws\Exception\AwsException;
use WPCOM_VIP\Aws\ResultInterface;
use WPCOM_VIP\GuzzleHttp\Psr7\Request;
use WPCOM_VIP\Psr\Http\Message\RequestInterface;
/**
 * @internal
 */
interface MonitoringMiddlewareInterface
{
    /**
     * Data for event properties to be sent to the monitoring agent.
     *
     * @param RequestInterface $request
     * @return array
     */
    public static function getRequestData(\WPCOM_VIP\Psr\Http\Message\RequestInterface $request);
    /**
     * Data for event properties to be sent to the monitoring agent.
     *
     * @param ResultInterface|AwsException|\Exception $klass
     * @return array
     */
    public static function getResponseData($klass);
    public function __invoke(\WPCOM_VIP\Aws\CommandInterface $cmd, \WPCOM_VIP\Psr\Http\Message\RequestInterface $request);
}

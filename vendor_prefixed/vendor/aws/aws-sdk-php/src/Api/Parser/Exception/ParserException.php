<?php

namespace WPCOM_VIP\Aws\Api\Parser\Exception;

use WPCOM_VIP\Aws\HasMonitoringEventsTrait;
use WPCOM_VIP\Aws\MonitoringEventsInterface;
use WPCOM_VIP\Aws\ResponseContainerInterface;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
class ParserException extends \RuntimeException implements \WPCOM_VIP\Aws\MonitoringEventsInterface, \WPCOM_VIP\Aws\ResponseContainerInterface
{
    use HasMonitoringEventsTrait;
    private $errorCode;
    private $requestId;
    private $response;
    public function __construct($message = '', $code = 0, $previous = null, array $context = [])
    {
        $this->errorCode = isset($context['error_code']) ? $context['error_code'] : null;
        $this->requestId = isset($context['request_id']) ? $context['request_id'] : null;
        $this->response = isset($context['response']) ? $context['response'] : null;
        parent::__construct($message, $code, $previous);
    }
    /**
     * Get the error code, if any.
     *
     * @return string|null
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
    /**
     * Get the request ID, if any.
     *
     * @return string|null
     */
    public function getRequestId()
    {
        return $this->requestId;
    }
    /**
     * Get the received HTTP response if any.
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }
}

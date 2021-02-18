<?php

namespace WPCOM_VIP\Aws\EndpointDiscovery\Exception;

use WPCOM_VIP\Aws\HasMonitoringEventsTrait;
use WPCOM_VIP\Aws\MonitoringEventsInterface;
/**
 * Represents an error interacting with configuration for endpoint discovery
 */
class ConfigurationException extends \RuntimeException implements \WPCOM_VIP\Aws\MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}

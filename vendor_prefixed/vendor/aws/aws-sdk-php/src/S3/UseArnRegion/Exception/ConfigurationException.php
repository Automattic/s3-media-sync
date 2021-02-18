<?php

namespace WPCOM_VIP\Aws\S3\UseArnRegion\Exception;

use WPCOM_VIP\Aws\HasMonitoringEventsTrait;
use WPCOM_VIP\Aws\MonitoringEventsInterface;
/**
 * Represents an error interacting with configuration for S3's UseArnRegion
 */
class ConfigurationException extends \RuntimeException implements \WPCOM_VIP\Aws\MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}

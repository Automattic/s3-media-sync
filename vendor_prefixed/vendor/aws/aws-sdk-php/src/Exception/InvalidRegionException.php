<?php

namespace WPCOM_VIP\Aws\Exception;

use WPCOM_VIP\Aws\HasMonitoringEventsTrait;
use WPCOM_VIP\Aws\MonitoringEventsInterface;
class InvalidRegionException extends \RuntimeException implements \WPCOM_VIP\Aws\MonitoringEventsInterface
{
    use HasMonitoringEventsTrait;
}
<?php

namespace WPCOM_VIP\Aws\Arn\S3;

use WPCOM_VIP\Aws\Arn\AccessPointArn as BaseAccessPointArn;
use WPCOM_VIP\Aws\Arn\ArnInterface;
use WPCOM_VIP\Aws\Arn\Exception\InvalidArnException;
/**
 * @internal
 */
class AccessPointArn extends \WPCOM_VIP\Aws\Arn\AccessPointArn implements \WPCOM_VIP\Aws\Arn\ArnInterface
{
    /**
     * Validation specific to AccessPointArn
     *
     * @param array $data
     */
    protected static function validate(array $data)
    {
        parent::validate($data);
        if ($data['service'] !== 's3') {
            throw new \WPCOM_VIP\Aws\Arn\Exception\InvalidArnException("The 3rd component of an S3 access" . " point ARN represents the region and must be 's3'.");
        }
    }
}

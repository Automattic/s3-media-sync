<?php

namespace WPCOM_VIP\Aws\DynamoDbStreams;

use WPCOM_VIP\Aws\AwsClient;
use WPCOM_VIP\Aws\DynamoDb\DynamoDbClient;
/**
 * This client is used to interact with the **Amazon DynamoDb Streams** service.
 *
 * @method \Aws\Result describeStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeStreamAsync(array $args = [])
 * @method \Aws\Result getRecords(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRecordsAsync(array $args = [])
 * @method \Aws\Result getShardIterator(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getShardIteratorAsync(array $args = [])
 * @method \Aws\Result listStreams(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listStreamsAsync(array $args = [])
 */
class DynamoDbStreamsClient extends \WPCOM_VIP\Aws\AwsClient
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['retries']['default'] = 11;
        $args['retries']['fn'] = [\WPCOM_VIP\Aws\DynamoDb\DynamoDbClient::class, '_applyRetryConfig'];
        return $args;
    }
}

<?php

namespace WPCOM_VIP\Aws\Kinesis;

use WPCOM_VIP\Aws\AwsClient;
/**
 * This client is used to interact with the **Amazon Kinesis** service.
 *
 * @method \Aws\Result addTagsToStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise addTagsToStreamAsync(array $args = [])
 * @method \Aws\Result createStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createStreamAsync(array $args = [])
 * @method \Aws\Result decreaseStreamRetentionPeriod(array $args = [])
 * @method \GuzzleHttp\Promise\Promise decreaseStreamRetentionPeriodAsync(array $args = [])
 * @method \Aws\Result deleteStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteStreamAsync(array $args = [])
 * @method \Aws\Result deregisterStreamConsumer(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deregisterStreamConsumerAsync(array $args = [])
 * @method \Aws\Result describeLimits(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeLimitsAsync(array $args = [])
 * @method \Aws\Result describeStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeStreamAsync(array $args = [])
 * @method \Aws\Result describeStreamConsumer(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeStreamConsumerAsync(array $args = [])
 * @method \Aws\Result describeStreamSummary(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeStreamSummaryAsync(array $args = [])
 * @method \Aws\Result disableEnhancedMonitoring(array $args = [])
 * @method \GuzzleHttp\Promise\Promise disableEnhancedMonitoringAsync(array $args = [])
 * @method \Aws\Result enableEnhancedMonitoring(array $args = [])
 * @method \GuzzleHttp\Promise\Promise enableEnhancedMonitoringAsync(array $args = [])
 * @method \Aws\Result getRecords(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getRecordsAsync(array $args = [])
 * @method \Aws\Result getShardIterator(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getShardIteratorAsync(array $args = [])
 * @method \Aws\Result increaseStreamRetentionPeriod(array $args = [])
 * @method \GuzzleHttp\Promise\Promise increaseStreamRetentionPeriodAsync(array $args = [])
 * @method \Aws\Result listShards(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listShardsAsync(array $args = [])
 * @method \Aws\Result listStreamConsumers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listStreamConsumersAsync(array $args = [])
 * @method \Aws\Result listStreams(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listStreamsAsync(array $args = [])
 * @method \Aws\Result listTagsForStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listTagsForStreamAsync(array $args = [])
 * @method \Aws\Result mergeShards(array $args = [])
 * @method \GuzzleHttp\Promise\Promise mergeShardsAsync(array $args = [])
 * @method \Aws\Result putRecord(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putRecordAsync(array $args = [])
 * @method \Aws\Result putRecords(array $args = [])
 * @method \GuzzleHttp\Promise\Promise putRecordsAsync(array $args = [])
 * @method \Aws\Result registerStreamConsumer(array $args = [])
 * @method \GuzzleHttp\Promise\Promise registerStreamConsumerAsync(array $args = [])
 * @method \Aws\Result removeTagsFromStream(array $args = [])
 * @method \GuzzleHttp\Promise\Promise removeTagsFromStreamAsync(array $args = [])
 * @method \Aws\Result splitShard(array $args = [])
 * @method \GuzzleHttp\Promise\Promise splitShardAsync(array $args = [])
 * @method \Aws\Result startStreamEncryption(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startStreamEncryptionAsync(array $args = [])
 * @method \Aws\Result stopStreamEncryption(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopStreamEncryptionAsync(array $args = [])
 * @method \Aws\Result updateShardCount(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateShardCountAsync(array $args = [])
 */
class KinesisClient extends \WPCOM_VIP\Aws\AwsClient
{
}
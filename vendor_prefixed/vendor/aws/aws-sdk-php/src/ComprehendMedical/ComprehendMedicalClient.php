<?php

namespace WPCOM_VIP\Aws\ComprehendMedical;

use WPCOM_VIP\Aws\AwsClient;
/**
 * This client is used to interact with the **AWS Comprehend Medical** service.
 * @method \Aws\Result describeEntitiesDetectionV2Job(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeEntitiesDetectionV2JobAsync(array $args = [])
 * @method \Aws\Result describeICD10CMInferenceJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeICD10CMInferenceJobAsync(array $args = [])
 * @method \Aws\Result describePHIDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describePHIDetectionJobAsync(array $args = [])
 * @method \Aws\Result describeRxNormInferenceJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeRxNormInferenceJobAsync(array $args = [])
 * @method \Aws\Result detectEntities(array $args = [])
 * @method \GuzzleHttp\Promise\Promise detectEntitiesAsync(array $args = [])
 * @method \Aws\Result detectEntitiesV2(array $args = [])
 * @method \GuzzleHttp\Promise\Promise detectEntitiesV2Async(array $args = [])
 * @method \Aws\Result detectPHI(array $args = [])
 * @method \GuzzleHttp\Promise\Promise detectPHIAsync(array $args = [])
 * @method \Aws\Result inferICD10CM(array $args = [])
 * @method \GuzzleHttp\Promise\Promise inferICD10CMAsync(array $args = [])
 * @method \Aws\Result inferRxNorm(array $args = [])
 * @method \GuzzleHttp\Promise\Promise inferRxNormAsync(array $args = [])
 * @method \Aws\Result listEntitiesDetectionV2Jobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEntitiesDetectionV2JobsAsync(array $args = [])
 * @method \Aws\Result listICD10CMInferenceJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listICD10CMInferenceJobsAsync(array $args = [])
 * @method \Aws\Result listPHIDetectionJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listPHIDetectionJobsAsync(array $args = [])
 * @method \Aws\Result listRxNormInferenceJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRxNormInferenceJobsAsync(array $args = [])
 * @method \Aws\Result startEntitiesDetectionV2Job(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startEntitiesDetectionV2JobAsync(array $args = [])
 * @method \Aws\Result startICD10CMInferenceJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startICD10CMInferenceJobAsync(array $args = [])
 * @method \Aws\Result startPHIDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startPHIDetectionJobAsync(array $args = [])
 * @method \Aws\Result startRxNormInferenceJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise startRxNormInferenceJobAsync(array $args = [])
 * @method \Aws\Result stopEntitiesDetectionV2Job(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopEntitiesDetectionV2JobAsync(array $args = [])
 * @method \Aws\Result stopICD10CMInferenceJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopICD10CMInferenceJobAsync(array $args = [])
 * @method \Aws\Result stopPHIDetectionJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopPHIDetectionJobAsync(array $args = [])
 * @method \Aws\Result stopRxNormInferenceJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise stopRxNormInferenceJobAsync(array $args = [])
 */
class ComprehendMedicalClient extends \WPCOM_VIP\Aws\AwsClient
{
}

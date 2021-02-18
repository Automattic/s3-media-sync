<?php

namespace WPCOM_VIP\Aws\ClientSideMonitoring;

use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\Aws\Credentials\CredentialsInterface;
use WPCOM_VIP\Aws\Exception\AwsException;
use WPCOM_VIP\Aws\ResponseContainerInterface;
use WPCOM_VIP\Aws\ResultInterface;
use WPCOM_VIP\Psr\Http\Message\RequestInterface;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
/**
 * @internal
 */
class ApiCallAttemptMonitoringMiddleware extends \WPCOM_VIP\Aws\ClientSideMonitoring\AbstractMonitoringMiddleware
{
    /**
     * Standard middleware wrapper function with CSM options passed in.
     *
     * @param callable $credentialProvider
     * @param mixed  $options
     * @param string $region
     * @param string $service
     * @return callable
     */
    public static function wrap(callable $credentialProvider, $options, $region, $service)
    {
        return function (callable $handler) use($credentialProvider, $options, $region, $service) {
            return new static($handler, $credentialProvider, $options, $region, $service);
        };
    }
    /**
     * {@inheritdoc}
     */
    public static function getRequestData(\WPCOM_VIP\Psr\Http\Message\RequestInterface $request)
    {
        return ['Fqdn' => $request->getUri()->getHost()];
    }
    /**
     * {@inheritdoc}
     */
    public static function getResponseData($klass)
    {
        if ($klass instanceof \WPCOM_VIP\Aws\ResultInterface) {
            return ['AttemptLatency' => self::getResultAttemptLatency($klass), 'DestinationIp' => self::getResultDestinationIp($klass), 'DnsLatency' => self::getResultDnsLatency($klass), 'HttpStatusCode' => self::getResultHttpStatusCode($klass), 'XAmzId2' => self::getResultHeader($klass, 'x-amz-id-2'), 'XAmzRequestId' => self::getResultHeader($klass, 'x-amz-request-id'), 'XAmznRequestId' => self::getResultHeader($klass, 'x-amzn-RequestId')];
        }
        if ($klass instanceof \WPCOM_VIP\Aws\Exception\AwsException) {
            return ['AttemptLatency' => self::getAwsExceptionAttemptLatency($klass), 'AwsException' => \substr(self::getAwsExceptionErrorCode($klass), 0, 128), 'AwsExceptionMessage' => \substr(self::getAwsExceptionMessage($klass), 0, 512), 'DestinationIp' => self::getAwsExceptionDestinationIp($klass), 'DnsLatency' => self::getAwsExceptionDnsLatency($klass), 'HttpStatusCode' => self::getAwsExceptionHttpStatusCode($klass), 'XAmzId2' => self::getAwsExceptionHeader($klass, 'x-amz-id-2'), 'XAmzRequestId' => self::getAwsExceptionHeader($klass, 'x-amz-request-id'), 'XAmznRequestId' => self::getAwsExceptionHeader($klass, 'x-amzn-RequestId')];
        }
        if ($klass instanceof \Exception) {
            return ['HttpStatusCode' => self::getExceptionHttpStatusCode($klass), 'SdkException' => \substr(self::getExceptionCode($klass), 0, 128), 'SdkExceptionMessage' => \substr(self::getExceptionMessage($klass), 0, 512), 'XAmzId2' => self::getExceptionHeader($klass, 'x-amz-id-2'), 'XAmzRequestId' => self::getExceptionHeader($klass, 'x-amz-request-id'), 'XAmznRequestId' => self::getExceptionHeader($klass, 'x-amzn-RequestId')];
        }
        throw new \InvalidArgumentException('Parameter must be an instance of ResultInterface, AwsException or Exception.');
    }
    private static function getResultAttemptLatency(\WPCOM_VIP\Aws\ResultInterface $result)
    {
        if (isset($result['@metadata']['transferStats']['http'])) {
            $attempt = \end($result['@metadata']['transferStats']['http']);
            if (isset($attempt['total_time'])) {
                return (int) \floor($attempt['total_time'] * 1000);
            }
        }
        return null;
    }
    private static function getResultDestinationIp(\WPCOM_VIP\Aws\ResultInterface $result)
    {
        if (isset($result['@metadata']['transferStats']['http'])) {
            $attempt = \end($result['@metadata']['transferStats']['http']);
            if (isset($attempt['primary_ip'])) {
                return $attempt['primary_ip'];
            }
        }
        return null;
    }
    private static function getResultDnsLatency(\WPCOM_VIP\Aws\ResultInterface $result)
    {
        if (isset($result['@metadata']['transferStats']['http'])) {
            $attempt = \end($result['@metadata']['transferStats']['http']);
            if (isset($attempt['namelookup_time'])) {
                return (int) \floor($attempt['namelookup_time'] * 1000);
            }
        }
        return null;
    }
    private static function getResultHttpStatusCode(\WPCOM_VIP\Aws\ResultInterface $result)
    {
        return $result['@metadata']['statusCode'];
    }
    private static function getAwsExceptionAttemptLatency(\WPCOM_VIP\Aws\Exception\AwsException $e)
    {
        $attempt = $e->getTransferInfo();
        if (isset($attempt['total_time'])) {
            return (int) \floor($attempt['total_time'] * 1000);
        }
        return null;
    }
    private static function getAwsExceptionErrorCode(\WPCOM_VIP\Aws\Exception\AwsException $e)
    {
        return $e->getAwsErrorCode();
    }
    private static function getAwsExceptionMessage(\WPCOM_VIP\Aws\Exception\AwsException $e)
    {
        return $e->getAwsErrorMessage();
    }
    private static function getAwsExceptionDestinationIp(\WPCOM_VIP\Aws\Exception\AwsException $e)
    {
        $attempt = $e->getTransferInfo();
        if (isset($attempt['primary_ip'])) {
            return $attempt['primary_ip'];
        }
        return null;
    }
    private static function getAwsExceptionDnsLatency(\WPCOM_VIP\Aws\Exception\AwsException $e)
    {
        $attempt = $e->getTransferInfo();
        if (isset($attempt['namelookup_time'])) {
            return (int) \floor($attempt['namelookup_time'] * 1000);
        }
        return null;
    }
    private static function getAwsExceptionHttpStatusCode(\WPCOM_VIP\Aws\Exception\AwsException $e)
    {
        $response = $e->getResponse();
        if ($response !== null) {
            return $response->getStatusCode();
        }
        return null;
    }
    private static function getExceptionHttpStatusCode(\Exception $e)
    {
        if ($e instanceof \WPCOM_VIP\Aws\ResponseContainerInterface) {
            $response = $e->getResponse();
            if ($response instanceof \WPCOM_VIP\Psr\Http\Message\ResponseInterface) {
                return $response->getStatusCode();
            }
        }
        return null;
    }
    private static function getExceptionCode(\Exception $e)
    {
        if (!$e instanceof \WPCOM_VIP\Aws\Exception\AwsException) {
            return \get_class($e);
        }
        return null;
    }
    private static function getExceptionMessage(\Exception $e)
    {
        if (!$e instanceof \WPCOM_VIP\Aws\Exception\AwsException) {
            return $e->getMessage();
        }
        return null;
    }
    /**
     * {@inheritdoc}
     */
    protected function populateRequestEventData(\WPCOM_VIP\Aws\CommandInterface $cmd, \WPCOM_VIP\Psr\Http\Message\RequestInterface $request, array $event)
    {
        $event = parent::populateRequestEventData($cmd, $request, $event);
        $event['Type'] = 'ApiCallAttempt';
        return $event;
    }
    /**
     * {@inheritdoc}
     */
    protected function populateResultEventData($result, array $event)
    {
        $event = parent::populateResultEventData($result, $event);
        $provider = $this->credentialProvider;
        /** @var CredentialsInterface $credentials */
        $credentials = $provider()->wait();
        $event['AccessKey'] = $credentials->getAccessKeyId();
        $sessionToken = $credentials->getSecurityToken();
        if ($sessionToken !== null) {
            $event['SessionToken'] = $sessionToken;
        }
        if (empty($event['AttemptLatency'])) {
            $event['AttemptLatency'] = (int) (\floor(\microtime(\true) * 1000) - $event['Timestamp']);
        }
        return $event;
    }
}
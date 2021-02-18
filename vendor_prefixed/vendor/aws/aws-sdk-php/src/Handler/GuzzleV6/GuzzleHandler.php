<?php

namespace WPCOM_VIP\Aws\Handler\GuzzleV6;

use Exception;
use WPCOM_VIP\GuzzleHttp\Exception\ConnectException;
use WPCOM_VIP\GuzzleHttp\Exception\RequestException;
use WPCOM_VIP\GuzzleHttp\Promise;
use WPCOM_VIP\GuzzleHttp\Client;
use WPCOM_VIP\GuzzleHttp\ClientInterface;
use WPCOM_VIP\GuzzleHttp\TransferStats;
use WPCOM_VIP\Psr\Http\Message\RequestInterface as Psr7Request;
/**
 * A request handler that sends PSR-7-compatible requests with Guzzle 6.
 */
class GuzzleHandler
{
    /** @var ClientInterface */
    private $client;
    /**
     * @param ClientInterface $client
     */
    public function __construct(\WPCOM_VIP\GuzzleHttp\ClientInterface $client = null)
    {
        $this->client = $client ?: new \WPCOM_VIP\GuzzleHttp\Client();
    }
    /**
     * @param Psr7Request $request
     * @param array       $options
     *
     * @return Promise\Promise
     */
    public function __invoke(\WPCOM_VIP\Psr\Http\Message\RequestInterface $request, array $options = [])
    {
        $request = $request->withHeader('User-Agent', $request->getHeaderLine('User-Agent') . ' ' . \WPCOM_VIP\GuzzleHttp\default_user_agent());
        return $this->client->sendAsync($request, $this->parseOptions($options))->otherwise(static function (\Exception $e) {
            $error = ['exception' => $e, 'connection_error' => $e instanceof \WPCOM_VIP\GuzzleHttp\Exception\ConnectException, 'response' => null];
            if ($e instanceof \WPCOM_VIP\GuzzleHttp\Exception\RequestException && $e->getResponse()) {
                $error['response'] = $e->getResponse();
            }
            return new \WPCOM_VIP\GuzzleHttp\Promise\RejectedPromise($error);
        });
    }
    private function parseOptions(array $options)
    {
        if (isset($options['http_stats_receiver'])) {
            $fn = $options['http_stats_receiver'];
            unset($options['http_stats_receiver']);
            $prev = isset($options['on_stats']) ? $options['on_stats'] : null;
            $options['on_stats'] = static function (\WPCOM_VIP\GuzzleHttp\TransferStats $stats) use($fn, $prev) {
                if (\is_callable($prev)) {
                    $prev($stats);
                }
                $transferStats = ['total_time' => $stats->getTransferTime()];
                $transferStats += $stats->getHandlerStats();
                $fn($transferStats);
            };
        }
        return $options;
    }
}

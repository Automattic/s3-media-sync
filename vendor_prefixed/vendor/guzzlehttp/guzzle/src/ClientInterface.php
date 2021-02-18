<?php

namespace WPCOM_VIP\GuzzleHttp;

use WPCOM_VIP\GuzzleHttp\Exception\GuzzleException;
use WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface;
use WPCOM_VIP\Psr\Http\Message\RequestInterface;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
use WPCOM_VIP\Psr\Http\Message\UriInterface;
/**
 * Client interface for sending HTTP requests.
 */
interface ClientInterface
{
    /**
     * The Guzzle major version.
     */
    const MAJOR_VERSION = 7;
    /**
     * Send an HTTP request.
     *
     * @param RequestInterface $request Request to send
     * @param array            $options Request options to apply to the given
     *                                  request and to the transfer.
     *
     * @throws GuzzleException
     */
    public function send(\WPCOM_VIP\Psr\Http\Message\RequestInterface $request, array $options = []) : \WPCOM_VIP\Psr\Http\Message\ResponseInterface;
    /**
     * Asynchronously send an HTTP request.
     *
     * @param RequestInterface $request Request to send
     * @param array            $options Request options to apply to the given
     *                                  request and to the transfer.
     */
    public function sendAsync(\WPCOM_VIP\Psr\Http\Message\RequestInterface $request, array $options = []) : \WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface;
    /**
     * Create and send an HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well.
     *
     * @param string              $method  HTTP method.
     * @param string|UriInterface $uri     URI object or string.
     * @param array               $options Request options to apply.
     *
     * @throws GuzzleException
     */
    public function request(string $method, $uri, array $options = []) : \WPCOM_VIP\Psr\Http\Message\ResponseInterface;
    /**
     * Create and send an asynchronous HTTP request.
     *
     * Use an absolute path to override the base path of the client, or a
     * relative path to append to the base path of the client. The URL can
     * contain the query string as well. Use an array to provide a URL
     * template and additional variables to use in the URL template expansion.
     *
     * @param string              $method  HTTP method
     * @param string|UriInterface $uri     URI object or string.
     * @param array               $options Request options to apply.
     */
    public function requestAsync(string $method, $uri, array $options = []) : \WPCOM_VIP\GuzzleHttp\Promise\PromiseInterface;
    /**
     * Get a client configuration option.
     *
     * These options include default request options of the client, a "handler"
     * (if utilized by the concrete client), and a "base_uri" if utilized by
     * the concrete client.
     *
     * @param string|null $option The config option to retrieve.
     *
     * @return mixed
     */
    public function getConfig(?string $option = null);
}

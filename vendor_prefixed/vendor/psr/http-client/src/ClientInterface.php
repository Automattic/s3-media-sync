<?php

namespace WPCOM_VIP\Psr\Http\Client;

use WPCOM_VIP\Psr\Http\Message\RequestInterface;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
interface ClientInterface
{
    /**
     * Sends a PSR-7 request and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws \Psr\Http\Client\ClientExceptionInterface If an error happens while processing the request.
     */
    public function sendRequest(\WPCOM_VIP\Psr\Http\Message\RequestInterface $request) : \WPCOM_VIP\Psr\Http\Message\ResponseInterface;
}

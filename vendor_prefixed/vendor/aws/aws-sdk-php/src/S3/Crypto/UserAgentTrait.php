<?php

namespace WPCOM_VIP\Aws\S3\Crypto;

use WPCOM_VIP\Aws\AwsClientInterface;
use WPCOM_VIP\Aws\Middleware;
use WPCOM_VIP\Psr\Http\Message\RequestInterface;
trait UserAgentTrait
{
    private function appendUserAgent(\WPCOM_VIP\Aws\AwsClientInterface $client, $agentString)
    {
        $list = $client->getHandlerList();
        $list->appendBuild(\WPCOM_VIP\Aws\Middleware::mapRequest(function (\WPCOM_VIP\Psr\Http\Message\RequestInterface $req) use($agentString) {
            if (!empty($req->getHeader('User-Agent')) && !empty($req->getHeader('User-Agent')[0])) {
                $userAgent = $req->getHeader('User-Agent')[0];
                if (\strpos($userAgent, $agentString) === \false) {
                    $userAgent .= " {$agentString}";
                }
            } else {
                $userAgent = $agentString;
            }
            $req = $req->withHeader('User-Agent', $userAgent);
            return $req;
        }));
    }
}

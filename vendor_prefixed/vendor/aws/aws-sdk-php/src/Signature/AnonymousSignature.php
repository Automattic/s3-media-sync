<?php

namespace WPCOM_VIP\Aws\Signature;

use WPCOM_VIP\Aws\Credentials\CredentialsInterface;
use WPCOM_VIP\Psr\Http\Message\RequestInterface;
/**
 * Provides anonymous client access (does not sign requests).
 */
class AnonymousSignature implements \WPCOM_VIP\Aws\Signature\SignatureInterface
{
    public function signRequest(\WPCOM_VIP\Psr\Http\Message\RequestInterface $request, \WPCOM_VIP\Aws\Credentials\CredentialsInterface $credentials)
    {
        return $request;
    }
    public function presign(\WPCOM_VIP\Psr\Http\Message\RequestInterface $request, \WPCOM_VIP\Aws\Credentials\CredentialsInterface $credentials, $expires, array $options = [])
    {
        return $request;
    }
}

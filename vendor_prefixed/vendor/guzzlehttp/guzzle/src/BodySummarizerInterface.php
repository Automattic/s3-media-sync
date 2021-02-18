<?php

namespace WPCOM_VIP\GuzzleHttp;

use WPCOM_VIP\Psr\Http\Message\MessageInterface;
interface BodySummarizerInterface
{
    /**
     * Returns a summarized message body.
     */
    public function summarize(\WPCOM_VIP\Psr\Http\Message\MessageInterface $message) : ?string;
}

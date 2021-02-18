<?php

namespace WPCOM_VIP\GuzzleHttp;

use WPCOM_VIP\Psr\Http\Message\MessageInterface;
final class BodySummarizer implements \WPCOM_VIP\GuzzleHttp\BodySummarizerInterface
{
    /**
     * @var int|null
     */
    private $truncateAt;
    public function __construct(int $truncateAt = null)
    {
        $this->truncateAt = $truncateAt;
    }
    /**
     * Returns a summarized message body.
     */
    public function summarize(\WPCOM_VIP\Psr\Http\Message\MessageInterface $message) : ?string
    {
        return $this->truncateAt === null ? \WPCOM_VIP\GuzzleHttp\Psr7\Message::bodySummary($message) : \WPCOM_VIP\GuzzleHttp\Psr7\Message::bodySummary($message, $this->truncateAt);
    }
}

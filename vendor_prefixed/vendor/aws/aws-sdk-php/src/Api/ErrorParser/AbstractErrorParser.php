<?php

namespace WPCOM_VIP\Aws\Api\ErrorParser;

use WPCOM_VIP\Aws\Api\Parser\MetadataParserTrait;
use WPCOM_VIP\Aws\Api\Parser\PayloadParserTrait;
use WPCOM_VIP\Aws\Api\Service;
use WPCOM_VIP\Aws\Api\StructureShape;
use WPCOM_VIP\Aws\CommandInterface;
use WPCOM_VIP\Psr\Http\Message\ResponseInterface;
abstract class AbstractErrorParser
{
    use MetadataParserTrait;
    use PayloadParserTrait;
    /**
     * @var Service
     */
    protected $api;
    /**
     * @param Service $api
     */
    public function __construct(\WPCOM_VIP\Aws\Api\Service $api = null)
    {
        $this->api = $api;
    }
    protected abstract function payload(\WPCOM_VIP\Psr\Http\Message\ResponseInterface $response, \WPCOM_VIP\Aws\Api\StructureShape $member);
    protected function extractPayload(\WPCOM_VIP\Aws\Api\StructureShape $member, \WPCOM_VIP\Psr\Http\Message\ResponseInterface $response)
    {
        if ($member instanceof \WPCOM_VIP\Aws\Api\StructureShape) {
            // Structure members parse top-level data into a specific key.
            return $this->payload($response, $member);
        } else {
            // Streaming data is just the stream from the response body.
            return $response->getBody();
        }
    }
    protected function populateShape(array &$data, \WPCOM_VIP\Psr\Http\Message\ResponseInterface $response, \WPCOM_VIP\Aws\CommandInterface $command = null)
    {
        $data['body'] = [];
        if (!empty($command) && !empty($this->api)) {
            // If modeled error code is indicated, check for known error shape
            if (!empty($data['code'])) {
                $errors = $this->api->getOperation($command->getName())->getErrors();
                foreach ($errors as $key => $error) {
                    // If error code matches a known error shape, populate the body
                    if ($data['code'] == $error['name'] && $error instanceof \WPCOM_VIP\Aws\Api\StructureShape) {
                        $modeledError = $error;
                        $data['body'] = $this->extractPayload($modeledError, $response);
                        $data['error_shape'] = $modeledError;
                        foreach ($error->getMembers() as $name => $member) {
                            switch ($member['location']) {
                                case 'header':
                                    $this->extractHeader($name, $member, $response, $data['body']);
                                    break;
                                case 'headers':
                                    $this->extractHeaders($name, $member, $response, $data['body']);
                                    break;
                                case 'statusCode':
                                    $this->extractStatus($name, $response, $data['body']);
                                    break;
                            }
                        }
                        break;
                    }
                }
            }
        }
        return $data;
    }
}

<?php

namespace WPCOM_VIP;

// This file was auto-generated from sdk-root/src/data/ivs/2020-07-14/api-2.json
return ['version' => '2.0', 'metadata' => ['apiVersion' => '2020-07-14', 'endpointPrefix' => 'ivs', 'protocol' => 'rest-json', 'serviceAbbreviation' => 'Amazon IVS', 'serviceFullName' => 'Amazon Interactive Video Service', 'serviceId' => 'ivs', 'signatureVersion' => 'v4', 'signingName' => 'ivs', 'uid' => 'ivs-2020-07-14'], 'operations' => ['BatchGetChannel' => ['name' => 'BatchGetChannel', 'http' => ['method' => 'POST', 'requestUri' => '/BatchGetChannel'], 'input' => ['shape' => 'BatchGetChannelRequest'], 'output' => ['shape' => 'BatchGetChannelResponse']], 'BatchGetStreamKey' => ['name' => 'BatchGetStreamKey', 'http' => ['method' => 'POST', 'requestUri' => '/BatchGetStreamKey'], 'input' => ['shape' => 'BatchGetStreamKeyRequest'], 'output' => ['shape' => 'BatchGetStreamKeyResponse']], 'CreateChannel' => ['name' => 'CreateChannel', 'http' => ['method' => 'POST', 'requestUri' => '/CreateChannel'], 'input' => ['shape' => 'CreateChannelRequest'], 'output' => ['shape' => 'CreateChannelResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ServiceQuotaExceededException'], ['shape' => 'PendingVerification']]], 'CreateStreamKey' => ['name' => 'CreateStreamKey', 'http' => ['method' => 'POST', 'requestUri' => '/CreateStreamKey'], 'input' => ['shape' => 'CreateStreamKeyRequest'], 'output' => ['shape' => 'CreateStreamKeyResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ServiceQuotaExceededException'], ['shape' => 'PendingVerification']]], 'DeleteChannel' => ['name' => 'DeleteChannel', 'http' => ['method' => 'POST', 'requestUri' => '/DeleteChannel'], 'input' => ['shape' => 'DeleteChannelRequest'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ConflictException'], ['shape' => 'PendingVerification']]], 'DeletePlaybackKeyPair' => ['name' => 'DeletePlaybackKeyPair', 'http' => ['method' => 'POST', 'requestUri' => '/DeletePlaybackKeyPair'], 'input' => ['shape' => 'DeletePlaybackKeyPairRequest'], 'output' => ['shape' => 'DeletePlaybackKeyPairResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'PendingVerification']]], 'DeleteStreamKey' => ['name' => 'DeleteStreamKey', 'http' => ['method' => 'POST', 'requestUri' => '/DeleteStreamKey'], 'input' => ['shape' => 'DeleteStreamKeyRequest'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'PendingVerification']]], 'GetChannel' => ['name' => 'GetChannel', 'http' => ['method' => 'POST', 'requestUri' => '/GetChannel'], 'input' => ['shape' => 'GetChannelRequest'], 'output' => ['shape' => 'GetChannelResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ResourceNotFoundException']]], 'GetPlaybackKeyPair' => ['name' => 'GetPlaybackKeyPair', 'http' => ['method' => 'POST', 'requestUri' => '/GetPlaybackKeyPair'], 'input' => ['shape' => 'GetPlaybackKeyPairRequest'], 'output' => ['shape' => 'GetPlaybackKeyPairResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ResourceNotFoundException']]], 'GetStream' => ['name' => 'GetStream', 'http' => ['method' => 'POST', 'requestUri' => '/GetStream'], 'input' => ['shape' => 'GetStreamRequest'], 'output' => ['shape' => 'GetStreamResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ChannelNotBroadcasting']]], 'GetStreamKey' => ['name' => 'GetStreamKey', 'http' => ['method' => 'POST', 'requestUri' => '/GetStreamKey'], 'input' => ['shape' => 'GetStreamKeyRequest'], 'output' => ['shape' => 'GetStreamKeyResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ResourceNotFoundException']]], 'ImportPlaybackKeyPair' => ['name' => 'ImportPlaybackKeyPair', 'http' => ['method' => 'POST', 'requestUri' => '/ImportPlaybackKeyPair'], 'input' => ['shape' => 'ImportPlaybackKeyPairRequest'], 'output' => ['shape' => 'ImportPlaybackKeyPairResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'ConflictException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ServiceQuotaExceededException'], ['shape' => 'PendingVerification']]], 'ListChannels' => ['name' => 'ListChannels', 'http' => ['method' => 'POST', 'requestUri' => '/ListChannels'], 'input' => ['shape' => 'ListChannelsRequest'], 'output' => ['shape' => 'ListChannelsResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException']]], 'ListPlaybackKeyPairs' => ['name' => 'ListPlaybackKeyPairs', 'http' => ['method' => 'POST', 'requestUri' => '/ListPlaybackKeyPairs'], 'input' => ['shape' => 'ListPlaybackKeyPairsRequest'], 'output' => ['shape' => 'ListPlaybackKeyPairsResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException']]], 'ListStreamKeys' => ['name' => 'ListStreamKeys', 'http' => ['method' => 'POST', 'requestUri' => '/ListStreamKeys'], 'input' => ['shape' => 'ListStreamKeysRequest'], 'output' => ['shape' => 'ListStreamKeysResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ResourceNotFoundException']]], 'ListStreams' => ['name' => 'ListStreams', 'http' => ['method' => 'POST', 'requestUri' => '/ListStreams'], 'input' => ['shape' => 'ListStreamsRequest'], 'output' => ['shape' => 'ListStreamsResponse'], 'errors' => [['shape' => 'AccessDeniedException']]], 'ListTagsForResource' => ['name' => 'ListTagsForResource', 'http' => ['method' => 'GET', 'requestUri' => '/tags/{resourceArn}'], 'input' => ['shape' => 'ListTagsForResourceRequest'], 'output' => ['shape' => 'ListTagsForResourceResponse'], 'errors' => [['shape' => 'InternalServerException'], ['shape' => 'ValidationException'], ['shape' => 'ResourceNotFoundException']]], 'PutMetadata' => ['name' => 'PutMetadata', 'http' => ['method' => 'POST', 'requestUri' => '/PutMetadata'], 'input' => ['shape' => 'PutMetadataRequest'], 'errors' => [['shape' => 'ThrottlingException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ChannelNotBroadcasting'], ['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException']]], 'StopStream' => ['name' => 'StopStream', 'http' => ['method' => 'POST', 'requestUri' => '/StopStream'], 'input' => ['shape' => 'StopStreamRequest'], 'output' => ['shape' => 'StopStreamResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ChannelNotBroadcasting'], ['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'StreamUnavailable']]], 'TagResource' => ['name' => 'TagResource', 'http' => ['method' => 'POST', 'requestUri' => '/tags/{resourceArn}'], 'input' => ['shape' => 'TagResourceRequest'], 'output' => ['shape' => 'TagResourceResponse'], 'errors' => [['shape' => 'InternalServerException'], ['shape' => 'ValidationException'], ['shape' => 'ResourceNotFoundException']]], 'UntagResource' => ['name' => 'UntagResource', 'http' => ['method' => 'DELETE', 'requestUri' => '/tags/{resourceArn}'], 'input' => ['shape' => 'UntagResourceRequest'], 'output' => ['shape' => 'UntagResourceResponse'], 'errors' => [['shape' => 'InternalServerException'], ['shape' => 'ValidationException'], ['shape' => 'ResourceNotFoundException']]], 'UpdateChannel' => ['name' => 'UpdateChannel', 'http' => ['method' => 'POST', 'requestUri' => '/UpdateChannel'], 'input' => ['shape' => 'UpdateChannelRequest'], 'output' => ['shape' => 'UpdateChannelResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ConflictException'], ['shape' => 'PendingVerification']]]], 'shapes' => ['AccessDeniedException' => ['type' => 'structure', 'members' => ['exceptionMessage' => ['shape' => 'errorMessage']], 'error' => ['httpStatusCode' => 403], 'exception' => \true], 'BatchError' => ['type' => 'structure', 'members' => ['arn' => ['shape' => 'ResourceArn'], 'code' => ['shape' => 'errorCode'], 'message' => ['shape' => 'errorMessage']]], 'BatchErrors' => ['type' => 'list', 'member' => ['shape' => 'BatchError']], 'BatchGetChannelRequest' => ['type' => 'structure', 'required' => ['arns'], 'members' => ['arns' => ['shape' => 'ChannelArnList']]], 'BatchGetChannelResponse' => ['type' => 'structure', 'members' => ['channels' => ['shape' => 'Channels'], 'errors' => ['shape' => 'BatchErrors']]], 'BatchGetStreamKeyRequest' => ['type' => 'structure', 'required' => ['arns'], 'members' => ['arns' => ['shape' => 'StreamKeyArnList']]], 'BatchGetStreamKeyResponse' => ['type' => 'structure', 'members' => ['streamKeys' => ['shape' => 'StreamKeys'], 'errors' => ['shape' => 'BatchErrors']]], 'Boolean' => ['type' => 'boolean'], 'Channel' => ['type' => 'structure', 'members' => ['arn' => ['shape' => 'ChannelArn'], 'name' => ['shape' => 'ChannelName'], 'latencyMode' => ['shape' => 'ChannelLatencyMode'], 'type' => ['shape' => 'ChannelType'], 'ingestEndpoint' => ['shape' => 'IngestEndpoint'], 'playbackUrl' => ['shape' => 'PlaybackURL'], 'authorized' => ['shape' => 'IsAuthorized'], 'tags' => ['shape' => 'Tags']]], 'ChannelArn' => ['type' => 'string', 'max' => 128, 'min' => 1, 'pattern' => '^arn:aws:[is]vs:[a-z0-9-]+:[0-9]+:channel/[a-zA-Z0-9-]+$'], 'ChannelArnList' => ['type' => 'list', 'member' => ['shape' => 'ChannelArn'], 'max' => 50, 'min' => 1], 'ChannelLatencyMode' => ['type' => 'string', 'enum' => ['NORMAL', 'LOW']], 'ChannelList' => ['type' => 'list', 'member' => ['shape' => 'ChannelSummary']], 'ChannelName' => ['type' => 'string', 'max' => 128, 'min' => 0, 'pattern' => '^[a-zA-Z0-9-_]*$'], 'ChannelNotBroadcasting' => ['type' => 'structure', 'members' => ['exceptionMessage' => ['shape' => 'errorMessage']], 'error' => ['httpStatusCode' => 404], 'exception' => \true], 'ChannelSummary' => ['type' => 'structure', 'members' => ['arn' => ['shape' => 'ChannelArn'], 'name' => ['shape' => 'ChannelName'], 'latencyMode' => ['shape' => 'ChannelLatencyMode'], 'authorized' => ['shape' => 'IsAuthorized'], 'tags' => ['shape' => 'Tags']]], 'ChannelType' => ['type' => 'string', 'enum' => ['BASIC', 'STANDARD']], 'Channels' => ['type' => 'list', 'member' => ['shape' => 'Channel']], 'ConflictException' => ['type' => 'structure', 'members' => ['exceptionMessage' => ['shape' => 'errorMessage']], 'error' => ['httpStatusCode' => 409], 'exception' => \true], 'CreateChannelRequest' => ['type' => 'structure', 'members' => ['name' => ['shape' => 'ChannelName'], 'latencyMode' => ['shape' => 'ChannelLatencyMode'], 'type' => ['shape' => 'ChannelType'], 'authorized' => ['shape' => 'Boolean'], 'tags' => ['shape' => 'Tags']]], 'CreateChannelResponse' => ['type' => 'structure', 'members' => ['channel' => ['shape' => 'Channel'], 'streamKey' => ['shape' => 'StreamKey']]], 'CreateStreamKeyRequest' => ['type' => 'structure', 'required' => ['channelArn'], 'members' => ['channelArn' => ['shape' => 'ChannelArn'], 'tags' => ['shape' => 'Tags']]], 'CreateStreamKeyResponse' => ['type' => 'structure', 'members' => ['streamKey' => ['shape' => 'StreamKey']]], 'DeleteChannelRequest' => ['type' => 'structure', 'required' => ['arn'], 'members' => ['arn' => ['shape' => 'ChannelArn']]], 'DeletePlaybackKeyPairRequest' => ['type' => 'structure', 'required' => ['arn'], 'members' => ['arn' => ['shape' => 'PlaybackKeyPairArn']]], 'DeletePlaybackKeyPairResponse' => ['type' => 'structure', 'members' => []], 'DeleteStreamKeyRequest' => ['type' => 'structure', 'required' => ['arn'], 'members' => ['arn' => ['shape' => 'StreamKeyArn']]], 'GetChannelRequest' => ['type' => 'structure', 'required' => ['arn'], 'members' => ['arn' => ['shape' => 'ChannelArn']]], 'GetChannelResponse' => ['type' => 'structure', 'members' => ['channel' => ['shape' => 'Channel']]], 'GetPlaybackKeyPairRequest' => ['type' => 'structure', 'required' => ['arn'], 'members' => ['arn' => ['shape' => 'PlaybackKeyPairArn']]], 'GetPlaybackKeyPairResponse' => ['type' => 'structure', 'members' => ['keyPair' => ['shape' => 'PlaybackKeyPair']]], 'GetStreamKeyRequest' => ['type' => 'structure', 'required' => ['arn'], 'members' => ['arn' => ['shape' => 'StreamKeyArn']]], 'GetStreamKeyResponse' => ['type' => 'structure', 'members' => ['streamKey' => ['shape' => 'StreamKey']]], 'GetStreamRequest' => ['type' => 'structure', 'required' => ['channelArn'], 'members' => ['channelArn' => ['shape' => 'ChannelArn']]], 'GetStreamResponse' => ['type' => 'structure', 'members' => ['stream' => ['shape' => 'Stream']]], 'ImportPlaybackKeyPairRequest' => ['type' => 'structure', 'required' => ['publicKeyMaterial'], 'members' => ['publicKeyMaterial' => ['shape' => 'PlaybackPublicKeyMaterial'], 'name' => ['shape' => 'PlaybackKeyPairName'], 'tags' => ['shape' => 'Tags']]], 'ImportPlaybackKeyPairResponse' => ['type' => 'structure', 'members' => ['keyPair' => ['shape' => 'PlaybackKeyPair']]], 'IngestEndpoint' => ['type' => 'string'], 'InternalServerException' => ['type' => 'structure', 'members' => ['exceptionMessage' => ['shape' => 'errorMessage']], 'error' => ['httpStatusCode' => 500], 'exception' => \true], 'IsAuthorized' => ['type' => 'boolean'], 'ListChannelsRequest' => ['type' => 'structure', 'members' => ['filterByName' => ['shape' => 'ChannelName'], 'nextToken' => ['shape' => 'PaginationToken'], 'maxResults' => ['shape' => 'MaxChannelResults']]], 'ListChannelsResponse' => ['type' => 'structure', 'required' => ['channels'], 'members' => ['channels' => ['shape' => 'ChannelList'], 'nextToken' => ['shape' => 'PaginationToken']]], 'ListPlaybackKeyPairsRequest' => ['type' => 'structure', 'members' => ['nextToken' => ['shape' => 'PaginationToken'], 'maxResults' => ['shape' => 'MaxPlaybackKeyPairResults']]], 'ListPlaybackKeyPairsResponse' => ['type' => 'structure', 'required' => ['keyPairs'], 'members' => ['keyPairs' => ['shape' => 'PlaybackKeyPairList'], 'nextToken' => ['shape' => 'PaginationToken']]], 'ListStreamKeysRequest' => ['type' => 'structure', 'required' => ['channelArn'], 'members' => ['channelArn' => ['shape' => 'ChannelArn'], 'nextToken' => ['shape' => 'PaginationToken'], 'maxResults' => ['shape' => 'MaxStreamKeyResults']]], 'ListStreamKeysResponse' => ['type' => 'structure', 'required' => ['streamKeys'], 'members' => ['streamKeys' => ['shape' => 'StreamKeyList'], 'nextToken' => ['shape' => 'PaginationToken']]], 'ListStreamsRequest' => ['type' => 'structure', 'members' => ['nextToken' => ['shape' => 'PaginationToken'], 'maxResults' => ['shape' => 'MaxStreamResults']]], 'ListStreamsResponse' => ['type' => 'structure', 'required' => ['streams'], 'members' => ['streams' => ['shape' => 'StreamList'], 'nextToken' => ['shape' => 'PaginationToken']]], 'ListTagsForResourceRequest' => ['type' => 'structure', 'required' => ['resourceArn'], 'members' => ['resourceArn' => ['shape' => 'ResourceArn', 'location' => 'uri', 'locationName' => 'resourceArn'], 'nextToken' => ['shape' => 'String'], 'maxResults' => ['shape' => 'MaxTagResults']]], 'ListTagsForResourceResponse' => ['type' => 'structure', 'required' => ['tags'], 'members' => ['tags' => ['shape' => 'Tags'], 'nextToken' => ['shape' => 'String']]], 'MaxChannelResults' => ['type' => 'integer', 'max' => 50, 'min' => 1], 'MaxPlaybackKeyPairResults' => ['type' => 'integer', 'max' => 50, 'min' => 1], 'MaxStreamKeyResults' => ['type' => 'integer', 'max' => 50, 'min' => 1], 'MaxStreamResults' => ['type' => 'integer', 'max' => 50, 'min' => 1], 'MaxTagResults' => ['type' => 'integer', 'max' => 50, 'min' => 1], 'PaginationToken' => ['type' => 'string', 'max' => 500, 'min' => 0], 'PendingVerification' => ['type' => 'structure', 'members' => ['exceptionMessage' => ['shape' => 'errorMessage']], 'error' => ['httpStatusCode' => 403], 'exception' => \true], 'PlaybackKeyPair' => ['type' => 'structure', 'members' => ['arn' => ['shape' => 'PlaybackKeyPairArn'], 'name' => ['shape' => 'PlaybackKeyPairName'], 'fingerprint' => ['shape' => 'PlaybackKeyPairFingerprint'], 'tags' => ['shape' => 'Tags']]], 'PlaybackKeyPairArn' => ['type' => 'string', 'max' => 128, 'min' => 1, 'pattern' => '^arn:aws:[is]vs:[a-z0-9-]+:[0-9]+:playback-key/[a-zA-Z0-9-]+$'], 'PlaybackKeyPairFingerprint' => ['type' => 'string'], 'PlaybackKeyPairList' => ['type' => 'list', 'member' => ['shape' => 'PlaybackKeyPairSummary']], 'PlaybackKeyPairName' => ['type' => 'string', 'max' => 128, 'min' => 0, 'pattern' => '^[a-zA-Z0-9-_]*$'], 'PlaybackKeyPairSummary' => ['type' => 'structure', 'members' => ['arn' => ['shape' => 'PlaybackKeyPairArn'], 'name' => ['shape' => 'PlaybackKeyPairName'], 'tags' => ['shape' => 'Tags']]], 'PlaybackPublicKeyMaterial' => ['type' => 'string'], 'PlaybackURL' => ['type' => 'string'], 'PutMetadataRequest' => ['type' => 'structure', 'required' => ['channelArn', 'metadata'], 'members' => ['channelArn' => ['shape' => 'ChannelArn'], 'metadata' => ['shape' => 'StreamMetadata']]], 'ResourceArn' => ['type' => 'string', 'max' => 128, 'min' => 1, 'pattern' => '^arn:aws:[is]vs:[a-z0-9-]+:[0-9]+:[a-z-]/[a-zA-Z0-9-]+$'], 'ResourceNotFoundException' => ['type' => 'structure', 'members' => ['exceptionMessage' => ['shape' => 'errorMessage']], 'error' => ['httpStatusCode' => 404], 'exception' => \true], 'ServiceQuotaExceededException' => ['type' => 'structure', 'members' => ['exceptionMessage' => ['shape' => 'errorMessage']], 'error' => ['httpStatusCode' => 402], 'exception' => \true], 'StopStreamRequest' => ['type' => 'structure', 'required' => ['channelArn'], 'members' => ['channelArn' => ['shape' => 'ChannelArn']]], 'StopStreamResponse' => ['type' => 'structure', 'members' => []], 'Stream' => ['type' => 'structure', 'members' => ['channelArn' => ['shape' => 'ChannelArn'], 'playbackUrl' => ['shape' => 'PlaybackURL'], 'startTime' => ['shape' => 'StreamStartTime'], 'state' => ['shape' => 'StreamState'], 'health' => ['shape' => 'StreamHealth'], 'viewerCount' => ['shape' => 'StreamViewerCount']]], 'StreamHealth' => ['type' => 'string', 'enum' => ['HEALTHY', 'STARVING', 'UNKNOWN']], 'StreamKey' => ['type' => 'structure', 'members' => ['arn' => ['shape' => 'StreamKeyArn'], 'value' => ['shape' => 'StreamKeyValue'], 'channelArn' => ['shape' => 'ChannelArn'], 'tags' => ['shape' => 'Tags']]], 'StreamKeyArn' => ['type' => 'string', 'max' => 128, 'min' => 1, 'pattern' => '^arn:aws:[is]vs:[a-z0-9-]+:[0-9]+:stream-key/[a-zA-Z0-9-]+$'], 'StreamKeyArnList' => ['type' => 'list', 'member' => ['shape' => 'StreamKeyArn'], 'max' => 50, 'min' => 1], 'StreamKeyList' => ['type' => 'list', 'member' => ['shape' => 'StreamKeySummary']], 'StreamKeySummary' => ['type' => 'structure', 'members' => ['arn' => ['shape' => 'StreamKeyArn'], 'channelArn' => ['shape' => 'ChannelArn'], 'tags' => ['shape' => 'Tags']]], 'StreamKeyValue' => ['type' => 'string'], 'StreamKeys' => ['type' => 'list', 'member' => ['shape' => 'StreamKey']], 'StreamList' => ['type' => 'list', 'member' => ['shape' => 'StreamSummary']], 'StreamMetadata' => ['type' => 'string'], 'StreamStartTime' => ['type' => 'timestamp'], 'StreamState' => ['type' => 'string', 'enum' => ['LIVE', 'OFFLINE']], 'StreamSummary' => ['type' => 'structure', 'members' => ['channelArn' => ['shape' => 'ChannelArn'], 'state' => ['shape' => 'StreamState'], 'health' => ['shape' => 'StreamHealth'], 'viewerCount' => ['shape' => 'StreamViewerCount'], 'startTime' => ['shape' => 'StreamStartTime']]], 'StreamUnavailable' => ['type' => 'structure', 'members' => ['exceptionMessage' => ['shape' => 'errorMessage']], 'error' => ['httpStatusCode' => 503], 'exception' => \true], 'StreamViewerCount' => ['type' => 'long'], 'String' => ['type' => 'string'], 'TagKey' => ['type' => 'string', 'max' => 128, 'min' => 1], 'TagKeyList' => ['type' => 'list', 'member' => ['shape' => 'TagKey'], 'max' => 50, 'min' => 0], 'TagResourceRequest' => ['type' => 'structure', 'required' => ['resourceArn', 'tags'], 'members' => ['resourceArn' => ['shape' => 'ResourceArn', 'location' => 'uri', 'locationName' => 'resourceArn'], 'tags' => ['shape' => 'Tags']]], 'TagResourceResponse' => ['type' => 'structure', 'members' => []], 'TagValue' => ['type' => 'string', 'max' => 256], 'Tags' => ['type' => 'map', 'key' => ['shape' => 'TagKey'], 'value' => ['shape' => 'TagValue'], 'max' => 50, 'min' => 0], 'ThrottlingException' => ['type' => 'structure', 'members' => ['exceptionMessage' => ['shape' => 'errorMessage']], 'error' => ['httpStatusCode' => 429], 'exception' => \true], 'UntagResourceRequest' => ['type' => 'structure', 'required' => ['resourceArn', 'tagKeys'], 'members' => ['resourceArn' => ['shape' => 'ResourceArn', 'location' => 'uri', 'locationName' => 'resourceArn'], 'tagKeys' => ['shape' => 'TagKeyList', 'location' => 'querystring', 'locationName' => 'tagKeys']]], 'UntagResourceResponse' => ['type' => 'structure', 'members' => []], 'UpdateChannelRequest' => ['type' => 'structure', 'required' => ['arn'], 'members' => ['arn' => ['shape' => 'ChannelArn'], 'name' => ['shape' => 'ChannelName'], 'latencyMode' => ['shape' => 'ChannelLatencyMode'], 'type' => ['shape' => 'ChannelType'], 'authorized' => ['shape' => 'Boolean']]], 'UpdateChannelResponse' => ['type' => 'structure', 'members' => ['channel' => ['shape' => 'Channel']]], 'ValidationException' => ['type' => 'structure', 'members' => ['exceptionMessage' => ['shape' => 'errorMessage']], 'error' => ['httpStatusCode' => 400], 'exception' => \true], 'errorCode' => ['type' => 'string'], 'errorMessage' => ['type' => 'string']]];

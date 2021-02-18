<?php

namespace WPCOM_VIP;

// This file was auto-generated from sdk-root/src/data/iotsecuretunneling/2018-10-05/api-2.json
return ['version' => '2.0', 'metadata' => ['apiVersion' => '2018-10-05', 'endpointPrefix' => 'api.tunneling.iot', 'jsonVersion' => '1.1', 'protocol' => 'json', 'serviceFullName' => 'AWS IoT Secure Tunneling', 'serviceId' => 'IoTSecureTunneling', 'signatureVersion' => 'v4', 'signingName' => 'IoTSecuredTunneling', 'targetPrefix' => 'IoTSecuredTunneling', 'uid' => 'iotsecuretunneling-2018-10-05'], 'operations' => ['CloseTunnel' => ['name' => 'CloseTunnel', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CloseTunnelRequest'], 'output' => ['shape' => 'CloseTunnelResponse'], 'errors' => [['shape' => 'ResourceNotFoundException']]], 'DescribeTunnel' => ['name' => 'DescribeTunnel', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeTunnelRequest'], 'output' => ['shape' => 'DescribeTunnelResponse'], 'errors' => [['shape' => 'ResourceNotFoundException']]], 'ListTagsForResource' => ['name' => 'ListTagsForResource', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListTagsForResourceRequest'], 'output' => ['shape' => 'ListTagsForResourceResponse'], 'errors' => [['shape' => 'ResourceNotFoundException']]], 'ListTunnels' => ['name' => 'ListTunnels', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListTunnelsRequest'], 'output' => ['shape' => 'ListTunnelsResponse']], 'OpenTunnel' => ['name' => 'OpenTunnel', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'OpenTunnelRequest'], 'output' => ['shape' => 'OpenTunnelResponse'], 'errors' => [['shape' => 'LimitExceededException']]], 'TagResource' => ['name' => 'TagResource', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'TagResourceRequest'], 'output' => ['shape' => 'TagResourceResponse'], 'errors' => [['shape' => 'ResourceNotFoundException']]], 'UntagResource' => ['name' => 'UntagResource', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UntagResourceRequest'], 'output' => ['shape' => 'UntagResourceResponse'], 'errors' => [['shape' => 'ResourceNotFoundException']]]], 'shapes' => ['AmazonResourceName' => ['type' => 'string', 'max' => 1011, 'min' => 1], 'ClientAccessToken' => ['type' => 'string', 'sensitive' => \true], 'CloseTunnelRequest' => ['type' => 'structure', 'required' => ['tunnelId'], 'members' => ['tunnelId' => ['shape' => 'TunnelId'], 'delete' => ['shape' => 'DeleteFlag', 'box' => \true]]], 'CloseTunnelResponse' => ['type' => 'structure', 'members' => []], 'ConnectionState' => ['type' => 'structure', 'members' => ['status' => ['shape' => 'ConnectionStatus'], 'lastUpdatedAt' => ['shape' => 'DateType']]], 'ConnectionStatus' => ['type' => 'string', 'enum' => ['CONNECTED', 'DISCONNECTED']], 'DateType' => ['type' => 'timestamp'], 'DeleteFlag' => ['type' => 'boolean'], 'DescribeTunnelRequest' => ['type' => 'structure', 'required' => ['tunnelId'], 'members' => ['tunnelId' => ['shape' => 'TunnelId']]], 'DescribeTunnelResponse' => ['type' => 'structure', 'members' => ['tunnel' => ['shape' => 'Tunnel']]], 'Description' => ['type' => 'string', 'pattern' => '[^\\p{C}]{1,2048}'], 'DestinationConfig' => ['type' => 'structure', 'required' => ['thingName', 'services'], 'members' => ['thingName' => ['shape' => 'ThingName'], 'services' => ['shape' => 'ServiceList']]], 'ErrorMessage' => ['type' => 'string'], 'LimitExceededException' => ['type' => 'structure', 'members' => ['message' => ['shape' => 'ErrorMessage']], 'exception' => \true], 'ListTagsForResourceRequest' => ['type' => 'structure', 'required' => ['resourceArn'], 'members' => ['resourceArn' => ['shape' => 'AmazonResourceName']]], 'ListTagsForResourceResponse' => ['type' => 'structure', 'members' => ['tags' => ['shape' => 'TagList']]], 'ListTunnelsRequest' => ['type' => 'structure', 'members' => ['thingName' => ['shape' => 'ThingName'], 'maxResults' => ['shape' => 'MaxResults', 'box' => \true], 'nextToken' => ['shape' => 'NextToken']]], 'ListTunnelsResponse' => ['type' => 'structure', 'members' => ['tunnelSummaries' => ['shape' => 'TunnelSummaryList'], 'nextToken' => ['shape' => 'NextToken']]], 'MaxResults' => ['type' => 'integer', 'max' => 100, 'min' => 1], 'NextToken' => ['type' => 'string', 'pattern' => '[a-zA-Z0-9_=-]{1,4096}'], 'OpenTunnelRequest' => ['type' => 'structure', 'members' => ['description' => ['shape' => 'Description'], 'tags' => ['shape' => 'TagList'], 'destinationConfig' => ['shape' => 'DestinationConfig'], 'timeoutConfig' => ['shape' => 'TimeoutConfig']]], 'OpenTunnelResponse' => ['type' => 'structure', 'members' => ['tunnelId' => ['shape' => 'TunnelId'], 'tunnelArn' => ['shape' => 'TunnelArn'], 'sourceAccessToken' => ['shape' => 'ClientAccessToken'], 'destinationAccessToken' => ['shape' => 'ClientAccessToken']]], 'ResourceNotFoundException' => ['type' => 'structure', 'members' => ['message' => ['shape' => 'ErrorMessage']], 'exception' => \true], 'Service' => ['type' => 'string', 'max' => 8, 'min' => 1, 'pattern' => '[a-zA-Z0-9:_-]+'], 'ServiceList' => ['type' => 'list', 'member' => ['shape' => 'Service'], 'max' => 1, 'min' => 1], 'Tag' => ['type' => 'structure', 'required' => ['key', 'value'], 'members' => ['key' => ['shape' => 'TagKey'], 'value' => ['shape' => 'TagValue']]], 'TagKey' => ['type' => 'string', 'max' => 128, 'min' => 1, 'pattern' => '^([\\p{L}\\p{Z}\\p{N}_.:/=+\\-@]*)$'], 'TagKeyList' => ['type' => 'list', 'member' => ['shape' => 'TagKey'], 'max' => 200, 'min' => 0], 'TagList' => ['type' => 'list', 'member' => ['shape' => 'Tag'], 'max' => 200, 'min' => 1], 'TagResourceRequest' => ['type' => 'structure', 'required' => ['resourceArn', 'tags'], 'members' => ['resourceArn' => ['shape' => 'AmazonResourceName'], 'tags' => ['shape' => 'TagList']]], 'TagResourceResponse' => ['type' => 'structure', 'members' => []], 'TagValue' => ['type' => 'string', 'max' => 256, 'min' => 0, 'pattern' => '^([\\p{L}\\p{Z}\\p{N}_.:/=+\\-@]*)$'], 'ThingName' => ['type' => 'string', 'max' => 128, 'min' => 1, 'pattern' => '[a-zA-Z0-9:_-]+'], 'TimeoutConfig' => ['type' => 'structure', 'members' => ['maxLifetimeTimeoutMinutes' => ['shape' => 'TimeoutInMin', 'box' => \true]]], 'TimeoutInMin' => ['type' => 'integer', 'max' => 720, 'min' => 1], 'Tunnel' => ['type' => 'structure', 'members' => ['tunnelId' => ['shape' => 'TunnelId'], 'tunnelArn' => ['shape' => 'TunnelArn'], 'status' => ['shape' => 'TunnelStatus'], 'sourceConnectionState' => ['shape' => 'ConnectionState'], 'destinationConnectionState' => ['shape' => 'ConnectionState'], 'description' => ['shape' => 'Description'], 'destinationConfig' => ['shape' => 'DestinationConfig'], 'timeoutConfig' => ['shape' => 'TimeoutConfig'], 'tags' => ['shape' => 'TagList'], 'createdAt' => ['shape' => 'DateType'], 'lastUpdatedAt' => ['shape' => 'DateType']]], 'TunnelArn' => ['type' => 'string', 'max' => 1600, 'min' => 1], 'TunnelId' => ['type' => 'string', 'pattern' => '[a-zA-Z0-9_\\-+=:]{1,128}'], 'TunnelStatus' => ['type' => 'string', 'enum' => ['OPEN', 'CLOSED']], 'TunnelSummary' => ['type' => 'structure', 'members' => ['tunnelId' => ['shape' => 'TunnelId'], 'tunnelArn' => ['shape' => 'TunnelArn'], 'status' => ['shape' => 'TunnelStatus'], 'description' => ['shape' => 'Description'], 'createdAt' => ['shape' => 'DateType'], 'lastUpdatedAt' => ['shape' => 'DateType']]], 'TunnelSummaryList' => ['type' => 'list', 'member' => ['shape' => 'TunnelSummary']], 'UntagResourceRequest' => ['type' => 'structure', 'required' => ['resourceArn', 'tagKeys'], 'members' => ['resourceArn' => ['shape' => 'AmazonResourceName'], 'tagKeys' => ['shape' => 'TagKeyList']]], 'UntagResourceResponse' => ['type' => 'structure', 'members' => []]]];

<?php

namespace WPCOM_VIP;

// This file was auto-generated from sdk-root/src/data/textract/2018-06-27/api-2.json
return ['version' => '2.0', 'metadata' => ['apiVersion' => '2018-06-27', 'endpointPrefix' => 'textract', 'jsonVersion' => '1.1', 'protocol' => 'json', 'serviceFullName' => 'Amazon Textract', 'serviceId' => 'Textract', 'signatureVersion' => 'v4', 'targetPrefix' => 'Textract', 'uid' => 'textract-2018-06-27'], 'operations' => ['AnalyzeDocument' => ['name' => 'AnalyzeDocument', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'AnalyzeDocumentRequest'], 'output' => ['shape' => 'AnalyzeDocumentResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'InvalidS3ObjectException'], ['shape' => 'UnsupportedDocumentException'], ['shape' => 'DocumentTooLargeException'], ['shape' => 'BadDocumentException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ProvisionedThroughputExceededException'], ['shape' => 'InternalServerError'], ['shape' => 'ThrottlingException'], ['shape' => 'HumanLoopQuotaExceededException']]], 'DetectDocumentText' => ['name' => 'DetectDocumentText', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DetectDocumentTextRequest'], 'output' => ['shape' => 'DetectDocumentTextResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'InvalidS3ObjectException'], ['shape' => 'UnsupportedDocumentException'], ['shape' => 'DocumentTooLargeException'], ['shape' => 'BadDocumentException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ProvisionedThroughputExceededException'], ['shape' => 'InternalServerError'], ['shape' => 'ThrottlingException']]], 'GetDocumentAnalysis' => ['name' => 'GetDocumentAnalysis', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetDocumentAnalysisRequest'], 'output' => ['shape' => 'GetDocumentAnalysisResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ProvisionedThroughputExceededException'], ['shape' => 'InvalidJobIdException'], ['shape' => 'InternalServerError'], ['shape' => 'ThrottlingException']]], 'GetDocumentTextDetection' => ['name' => 'GetDocumentTextDetection', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetDocumentTextDetectionRequest'], 'output' => ['shape' => 'GetDocumentTextDetectionResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ProvisionedThroughputExceededException'], ['shape' => 'InvalidJobIdException'], ['shape' => 'InternalServerError'], ['shape' => 'ThrottlingException']]], 'StartDocumentAnalysis' => ['name' => 'StartDocumentAnalysis', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'StartDocumentAnalysisRequest'], 'output' => ['shape' => 'StartDocumentAnalysisResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'InvalidS3ObjectException'], ['shape' => 'UnsupportedDocumentException'], ['shape' => 'DocumentTooLargeException'], ['shape' => 'BadDocumentException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ProvisionedThroughputExceededException'], ['shape' => 'InternalServerError'], ['shape' => 'IdempotentParameterMismatchException'], ['shape' => 'ThrottlingException'], ['shape' => 'LimitExceededException']]], 'StartDocumentTextDetection' => ['name' => 'StartDocumentTextDetection', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'StartDocumentTextDetectionRequest'], 'output' => ['shape' => 'StartDocumentTextDetectionResponse'], 'errors' => [['shape' => 'InvalidParameterException'], ['shape' => 'InvalidS3ObjectException'], ['shape' => 'UnsupportedDocumentException'], ['shape' => 'DocumentTooLargeException'], ['shape' => 'BadDocumentException'], ['shape' => 'AccessDeniedException'], ['shape' => 'ProvisionedThroughputExceededException'], ['shape' => 'InternalServerError'], ['shape' => 'IdempotentParameterMismatchException'], ['shape' => 'ThrottlingException'], ['shape' => 'LimitExceededException']]]], 'shapes' => ['AccessDeniedException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'AnalyzeDocumentRequest' => ['type' => 'structure', 'required' => ['Document', 'FeatureTypes'], 'members' => ['Document' => ['shape' => 'Document'], 'FeatureTypes' => ['shape' => 'FeatureTypes'], 'HumanLoopConfig' => ['shape' => 'HumanLoopConfig']]], 'AnalyzeDocumentResponse' => ['type' => 'structure', 'members' => ['DocumentMetadata' => ['shape' => 'DocumentMetadata'], 'Blocks' => ['shape' => 'BlockList'], 'HumanLoopActivationOutput' => ['shape' => 'HumanLoopActivationOutput'], 'AnalyzeDocumentModelVersion' => ['shape' => 'String']]], 'BadDocumentException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'Block' => ['type' => 'structure', 'members' => ['BlockType' => ['shape' => 'BlockType'], 'Confidence' => ['shape' => 'Percent'], 'Text' => ['shape' => 'String'], 'RowIndex' => ['shape' => 'UInteger'], 'ColumnIndex' => ['shape' => 'UInteger'], 'RowSpan' => ['shape' => 'UInteger'], 'ColumnSpan' => ['shape' => 'UInteger'], 'Geometry' => ['shape' => 'Geometry'], 'Id' => ['shape' => 'NonEmptyString'], 'Relationships' => ['shape' => 'RelationshipList'], 'EntityTypes' => ['shape' => 'EntityTypes'], 'SelectionStatus' => ['shape' => 'SelectionStatus'], 'Page' => ['shape' => 'UInteger']]], 'BlockList' => ['type' => 'list', 'member' => ['shape' => 'Block']], 'BlockType' => ['type' => 'string', 'enum' => ['KEY_VALUE_SET', 'PAGE', 'LINE', 'WORD', 'TABLE', 'CELL', 'SELECTION_ELEMENT']], 'BoundingBox' => ['type' => 'structure', 'members' => ['Width' => ['shape' => 'Float'], 'Height' => ['shape' => 'Float'], 'Left' => ['shape' => 'Float'], 'Top' => ['shape' => 'Float']]], 'ClientRequestToken' => ['type' => 'string', 'max' => 64, 'min' => 1, 'pattern' => '^[a-zA-Z0-9-_]+$'], 'ContentClassifier' => ['type' => 'string', 'enum' => ['FreeOfPersonallyIdentifiableInformation', 'FreeOfAdultContent']], 'ContentClassifiers' => ['type' => 'list', 'member' => ['shape' => 'ContentClassifier'], 'max' => 256], 'DetectDocumentTextRequest' => ['type' => 'structure', 'required' => ['Document'], 'members' => ['Document' => ['shape' => 'Document']]], 'DetectDocumentTextResponse' => ['type' => 'structure', 'members' => ['DocumentMetadata' => ['shape' => 'DocumentMetadata'], 'Blocks' => ['shape' => 'BlockList'], 'DetectDocumentTextModelVersion' => ['shape' => 'String']]], 'Document' => ['type' => 'structure', 'members' => ['Bytes' => ['shape' => 'ImageBlob'], 'S3Object' => ['shape' => 'S3Object']]], 'DocumentLocation' => ['type' => 'structure', 'members' => ['S3Object' => ['shape' => 'S3Object']]], 'DocumentMetadata' => ['type' => 'structure', 'members' => ['Pages' => ['shape' => 'UInteger']]], 'DocumentTooLargeException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'EntityType' => ['type' => 'string', 'enum' => ['KEY', 'VALUE']], 'EntityTypes' => ['type' => 'list', 'member' => ['shape' => 'EntityType']], 'ErrorCode' => ['type' => 'string'], 'FeatureType' => ['type' => 'string', 'enum' => ['TABLES', 'FORMS']], 'FeatureTypes' => ['type' => 'list', 'member' => ['shape' => 'FeatureType']], 'Float' => ['type' => 'float'], 'FlowDefinitionArn' => ['type' => 'string', 'max' => 256], 'Geometry' => ['type' => 'structure', 'members' => ['BoundingBox' => ['shape' => 'BoundingBox'], 'Polygon' => ['shape' => 'Polygon']]], 'GetDocumentAnalysisRequest' => ['type' => 'structure', 'required' => ['JobId'], 'members' => ['JobId' => ['shape' => 'JobId'], 'MaxResults' => ['shape' => 'MaxResults'], 'NextToken' => ['shape' => 'PaginationToken']]], 'GetDocumentAnalysisResponse' => ['type' => 'structure', 'members' => ['DocumentMetadata' => ['shape' => 'DocumentMetadata'], 'JobStatus' => ['shape' => 'JobStatus'], 'NextToken' => ['shape' => 'PaginationToken'], 'Blocks' => ['shape' => 'BlockList'], 'Warnings' => ['shape' => 'Warnings'], 'StatusMessage' => ['shape' => 'StatusMessage'], 'AnalyzeDocumentModelVersion' => ['shape' => 'String']]], 'GetDocumentTextDetectionRequest' => ['type' => 'structure', 'required' => ['JobId'], 'members' => ['JobId' => ['shape' => 'JobId'], 'MaxResults' => ['shape' => 'MaxResults'], 'NextToken' => ['shape' => 'PaginationToken']]], 'GetDocumentTextDetectionResponse' => ['type' => 'structure', 'members' => ['DocumentMetadata' => ['shape' => 'DocumentMetadata'], 'JobStatus' => ['shape' => 'JobStatus'], 'NextToken' => ['shape' => 'PaginationToken'], 'Blocks' => ['shape' => 'BlockList'], 'Warnings' => ['shape' => 'Warnings'], 'StatusMessage' => ['shape' => 'StatusMessage'], 'DetectDocumentTextModelVersion' => ['shape' => 'String']]], 'HumanLoopActivationConditionsEvaluationResults' => ['type' => 'string', 'max' => 10240], 'HumanLoopActivationOutput' => ['type' => 'structure', 'members' => ['HumanLoopArn' => ['shape' => 'HumanLoopArn'], 'HumanLoopActivationReasons' => ['shape' => 'HumanLoopActivationReasons'], 'HumanLoopActivationConditionsEvaluationResults' => ['shape' => 'HumanLoopActivationConditionsEvaluationResults', 'jsonvalue' => \true]]], 'HumanLoopActivationReason' => ['type' => 'string'], 'HumanLoopActivationReasons' => ['type' => 'list', 'member' => ['shape' => 'HumanLoopActivationReason'], 'min' => 1], 'HumanLoopArn' => ['type' => 'string', 'max' => 256], 'HumanLoopConfig' => ['type' => 'structure', 'required' => ['HumanLoopName', 'FlowDefinitionArn'], 'members' => ['HumanLoopName' => ['shape' => 'HumanLoopName'], 'FlowDefinitionArn' => ['shape' => 'FlowDefinitionArn'], 'DataAttributes' => ['shape' => 'HumanLoopDataAttributes']]], 'HumanLoopDataAttributes' => ['type' => 'structure', 'members' => ['ContentClassifiers' => ['shape' => 'ContentClassifiers']]], 'HumanLoopName' => ['type' => 'string', 'max' => 63, 'min' => 1, 'pattern' => '^[a-z0-9](-*[a-z0-9])*'], 'HumanLoopQuotaExceededException' => ['type' => 'structure', 'members' => ['ResourceType' => ['shape' => 'String'], 'QuotaCode' => ['shape' => 'String'], 'ServiceCode' => ['shape' => 'String']], 'exception' => \true], 'IdList' => ['type' => 'list', 'member' => ['shape' => 'NonEmptyString']], 'IdempotentParameterMismatchException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'ImageBlob' => ['type' => 'blob', 'max' => 10485760, 'min' => 1], 'InternalServerError' => ['type' => 'structure', 'members' => [], 'exception' => \true, 'fault' => \true], 'InvalidJobIdException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'InvalidParameterException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'InvalidS3ObjectException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'JobId' => ['type' => 'string', 'max' => 64, 'min' => 1, 'pattern' => '^[a-zA-Z0-9-_]+$'], 'JobStatus' => ['type' => 'string', 'enum' => ['IN_PROGRESS', 'SUCCEEDED', 'FAILED', 'PARTIAL_SUCCESS']], 'JobTag' => ['type' => 'string', 'max' => 64, 'min' => 1, 'pattern' => '[a-zA-Z0-9_.\\-:]+'], 'LimitExceededException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'MaxResults' => ['type' => 'integer', 'min' => 1], 'NonEmptyString' => ['type' => 'string', 'pattern' => '.*\\S.*'], 'NotificationChannel' => ['type' => 'structure', 'required' => ['SNSTopicArn', 'RoleArn'], 'members' => ['SNSTopicArn' => ['shape' => 'SNSTopicArn'], 'RoleArn' => ['shape' => 'RoleArn']]], 'Pages' => ['type' => 'list', 'member' => ['shape' => 'UInteger']], 'PaginationToken' => ['type' => 'string', 'max' => 255, 'min' => 1, 'pattern' => '.*\\S.*'], 'Percent' => ['type' => 'float', 'max' => 100, 'min' => 0], 'Point' => ['type' => 'structure', 'members' => ['X' => ['shape' => 'Float'], 'Y' => ['shape' => 'Float']]], 'Polygon' => ['type' => 'list', 'member' => ['shape' => 'Point']], 'ProvisionedThroughputExceededException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'Relationship' => ['type' => 'structure', 'members' => ['Type' => ['shape' => 'RelationshipType'], 'Ids' => ['shape' => 'IdList']]], 'RelationshipList' => ['type' => 'list', 'member' => ['shape' => 'Relationship']], 'RelationshipType' => ['type' => 'string', 'enum' => ['VALUE', 'CHILD']], 'RoleArn' => ['type' => 'string', 'max' => 2048, 'min' => 20, 'pattern' => 'arn:([a-z\\d-]+):iam::\\d{12}:role/?[a-zA-Z_0-9+=,.@\\-_/]+'], 'S3Bucket' => ['type' => 'string', 'max' => 255, 'min' => 3, 'pattern' => '[0-9A-Za-z\\.\\-_]*'], 'S3Object' => ['type' => 'structure', 'members' => ['Bucket' => ['shape' => 'S3Bucket'], 'Name' => ['shape' => 'S3ObjectName'], 'Version' => ['shape' => 'S3ObjectVersion']]], 'S3ObjectName' => ['type' => 'string', 'max' => 1024, 'min' => 1, 'pattern' => '.*\\S.*'], 'S3ObjectVersion' => ['type' => 'string', 'max' => 1024, 'min' => 1, 'pattern' => '.*\\S.*'], 'SNSTopicArn' => ['type' => 'string', 'max' => 1024, 'min' => 20, 'pattern' => '(^arn:([a-z\\d-]+):sns:[a-zA-Z\\d-]{1,20}:\\w{12}:.+$)'], 'SelectionStatus' => ['type' => 'string', 'enum' => ['SELECTED', 'NOT_SELECTED']], 'StartDocumentAnalysisRequest' => ['type' => 'structure', 'required' => ['DocumentLocation', 'FeatureTypes'], 'members' => ['DocumentLocation' => ['shape' => 'DocumentLocation'], 'FeatureTypes' => ['shape' => 'FeatureTypes'], 'ClientRequestToken' => ['shape' => 'ClientRequestToken'], 'JobTag' => ['shape' => 'JobTag'], 'NotificationChannel' => ['shape' => 'NotificationChannel']]], 'StartDocumentAnalysisResponse' => ['type' => 'structure', 'members' => ['JobId' => ['shape' => 'JobId']]], 'StartDocumentTextDetectionRequest' => ['type' => 'structure', 'required' => ['DocumentLocation'], 'members' => ['DocumentLocation' => ['shape' => 'DocumentLocation'], 'ClientRequestToken' => ['shape' => 'ClientRequestToken'], 'JobTag' => ['shape' => 'JobTag'], 'NotificationChannel' => ['shape' => 'NotificationChannel']]], 'StartDocumentTextDetectionResponse' => ['type' => 'structure', 'members' => ['JobId' => ['shape' => 'JobId']]], 'StatusMessage' => ['type' => 'string'], 'String' => ['type' => 'string'], 'ThrottlingException' => ['type' => 'structure', 'members' => [], 'exception' => \true, 'fault' => \true], 'UInteger' => ['type' => 'integer', 'min' => 0], 'UnsupportedDocumentException' => ['type' => 'structure', 'members' => [], 'exception' => \true], 'Warning' => ['type' => 'structure', 'members' => ['ErrorCode' => ['shape' => 'ErrorCode'], 'Pages' => ['shape' => 'Pages']]], 'Warnings' => ['type' => 'list', 'member' => ['shape' => 'Warning']]]];

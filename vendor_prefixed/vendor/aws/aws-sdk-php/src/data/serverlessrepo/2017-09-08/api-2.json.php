<?php

namespace WPCOM_VIP;

// This file was auto-generated from sdk-root/src/data/serverlessrepo/2017-09-08/api-2.json
return ['metadata' => ['apiVersion' => '2017-09-08', 'endpointPrefix' => 'serverlessrepo', 'signingName' => 'serverlessrepo', 'serviceFullName' => 'AWSServerlessApplicationRepository', 'serviceId' => 'ServerlessApplicationRepository', 'protocol' => 'rest-json', 'jsonVersion' => '1.1', 'uid' => 'serverlessrepo-2017-09-08', 'signatureVersion' => 'v4'], 'operations' => ['CreateApplication' => ['name' => 'CreateApplication', 'http' => ['method' => 'POST', 'requestUri' => '/applications', 'responseCode' => 201], 'input' => ['shape' => 'CreateApplicationRequest'], 'output' => ['shape' => 'CreateApplicationResponse'], 'errors' => [['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ConflictException'], ['shape' => 'ForbiddenException']]], 'CreateApplicationVersion' => ['name' => 'CreateApplicationVersion', 'http' => ['method' => 'PUT', 'requestUri' => '/applications/{applicationId}/versions/{semanticVersion}', 'responseCode' => 201], 'input' => ['shape' => 'CreateApplicationVersionRequest'], 'output' => ['shape' => 'CreateApplicationVersionResponse'], 'errors' => [['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ConflictException'], ['shape' => 'ForbiddenException']]], 'CreateCloudFormationChangeSet' => ['name' => 'CreateCloudFormationChangeSet', 'http' => ['method' => 'POST', 'requestUri' => '/applications/{applicationId}/changesets', 'responseCode' => 201], 'input' => ['shape' => 'CreateCloudFormationChangeSetRequest'], 'output' => ['shape' => 'CreateCloudFormationChangeSetResponse'], 'errors' => [['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException']]], 'CreateCloudFormationTemplate' => ['name' => 'CreateCloudFormationTemplate', 'http' => ['method' => 'POST', 'requestUri' => '/applications/{applicationId}/templates', 'responseCode' => 201], 'input' => ['shape' => 'CreateCloudFormationTemplateRequest'], 'output' => ['shape' => 'CreateCloudFormationTemplateResponse'], 'errors' => [['shape' => 'NotFoundException'], ['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException']]], 'DeleteApplication' => ['name' => 'DeleteApplication', 'http' => ['method' => 'DELETE', 'requestUri' => '/applications/{applicationId}', 'responseCode' => 204], 'input' => ['shape' => 'DeleteApplicationRequest'], 'errors' => [['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException'], ['shape' => 'NotFoundException'], ['shape' => 'TooManyRequestsException'], ['shape' => 'ConflictException']]], 'GetApplication' => ['name' => 'GetApplication', 'http' => ['method' => 'GET', 'requestUri' => '/applications/{applicationId}', 'responseCode' => 200], 'input' => ['shape' => 'GetApplicationRequest'], 'output' => ['shape' => 'GetApplicationResponse'], 'errors' => [['shape' => 'NotFoundException'], ['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException']]], 'GetApplicationPolicy' => ['name' => 'GetApplicationPolicy', 'http' => ['method' => 'GET', 'requestUri' => '/applications/{applicationId}/policy', 'responseCode' => 200], 'input' => ['shape' => 'GetApplicationPolicyRequest'], 'output' => ['shape' => 'GetApplicationPolicyResponse'], 'errors' => [['shape' => 'NotFoundException'], ['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException']]], 'GetCloudFormationTemplate' => ['name' => 'GetCloudFormationTemplate', 'http' => ['method' => 'GET', 'requestUri' => '/applications/{applicationId}/templates/{templateId}', 'responseCode' => 200], 'input' => ['shape' => 'GetCloudFormationTemplateRequest'], 'output' => ['shape' => 'GetCloudFormationTemplateResponse'], 'errors' => [['shape' => 'NotFoundException'], ['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException']]], 'ListApplicationDependencies' => ['name' => 'ListApplicationDependencies', 'http' => ['method' => 'GET', 'requestUri' => '/applications/{applicationId}/dependencies', 'responseCode' => 200], 'input' => ['shape' => 'ListApplicationDependenciesRequest'], 'output' => ['shape' => 'ListApplicationDependenciesResponse'], 'errors' => [['shape' => 'NotFoundException'], ['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException']]], 'ListApplicationVersions' => ['name' => 'ListApplicationVersions', 'http' => ['method' => 'GET', 'requestUri' => '/applications/{applicationId}/versions', 'responseCode' => 200], 'input' => ['shape' => 'ListApplicationVersionsRequest'], 'output' => ['shape' => 'ListApplicationVersionsResponse'], 'errors' => [['shape' => 'NotFoundException'], ['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException']]], 'ListApplications' => ['name' => 'ListApplications', 'http' => ['method' => 'GET', 'requestUri' => '/applications', 'responseCode' => 200], 'input' => ['shape' => 'ListApplicationsRequest'], 'output' => ['shape' => 'ListApplicationsResponse'], 'errors' => [['shape' => 'NotFoundException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException']]], 'PutApplicationPolicy' => ['name' => 'PutApplicationPolicy', 'http' => ['method' => 'PUT', 'requestUri' => '/applications/{applicationId}/policy', 'responseCode' => 200], 'input' => ['shape' => 'PutApplicationPolicyRequest'], 'output' => ['shape' => 'PutApplicationPolicyResponse'], 'errors' => [['shape' => 'NotFoundException'], ['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException']]], 'UnshareApplication' => ['name' => 'UnshareApplication', 'http' => ['method' => 'POST', 'requestUri' => '/applications/{applicationId}/unshare', 'responseCode' => 204], 'input' => ['shape' => 'UnshareApplicationRequest'], 'errors' => [['shape' => 'NotFoundException'], ['shape' => 'TooManyRequestsException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException']]], 'UpdateApplication' => ['name' => 'UpdateApplication', 'http' => ['method' => 'PATCH', 'requestUri' => '/applications/{applicationId}', 'responseCode' => 200], 'input' => ['shape' => 'UpdateApplicationRequest'], 'output' => ['shape' => 'UpdateApplicationResponse'], 'errors' => [['shape' => 'BadRequestException'], ['shape' => 'InternalServerErrorException'], ['shape' => 'ForbiddenException'], ['shape' => 'NotFoundException'], ['shape' => 'TooManyRequestsException'], ['shape' => 'ConflictException']]]], 'shapes' => ['Application' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'Author' => ['shape' => '__string', 'locationName' => 'author'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'HomePageUrl' => ['shape' => '__string', 'locationName' => 'homePageUrl'], 'IsVerifiedAuthor' => ['shape' => '__boolean', 'locationName' => 'isVerifiedAuthor'], 'Labels' => ['shape' => '__listOf__string', 'locationName' => 'labels'], 'LicenseUrl' => ['shape' => '__string', 'locationName' => 'licenseUrl'], 'Name' => ['shape' => '__string', 'locationName' => 'name'], 'ReadmeUrl' => ['shape' => '__string', 'locationName' => 'readmeUrl'], 'SpdxLicenseId' => ['shape' => '__string', 'locationName' => 'spdxLicenseId'], 'VerifiedAuthorUrl' => ['shape' => '__string', 'locationName' => 'verifiedAuthorUrl'], 'Version' => ['shape' => 'Version', 'locationName' => 'version']], 'required' => ['Description', 'Author', 'ApplicationId', 'Name']], 'ApplicationDependencyPage' => ['type' => 'structure', 'members' => ['Dependencies' => ['shape' => '__listOfApplicationDependencySummary', 'locationName' => 'dependencies'], 'NextToken' => ['shape' => '__string', 'locationName' => 'nextToken']], 'required' => ['Dependencies']], 'ApplicationDependencySummary' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion']], 'required' => ['ApplicationId', 'SemanticVersion']], 'ApplicationPage' => ['type' => 'structure', 'members' => ['Applications' => ['shape' => '__listOfApplicationSummary', 'locationName' => 'applications'], 'NextToken' => ['shape' => '__string', 'locationName' => 'nextToken']], 'required' => ['Applications']], 'ApplicationPolicy' => ['type' => 'structure', 'members' => ['Statements' => ['shape' => '__listOfApplicationPolicyStatement', 'locationName' => 'statements']], 'required' => ['Statements']], 'ApplicationPolicyStatement' => ['type' => 'structure', 'members' => ['Actions' => ['shape' => '__listOf__string', 'locationName' => 'actions'], 'PrincipalOrgIDs' => ['shape' => '__listOf__string', 'locationName' => 'principalOrgIDs'], 'Principals' => ['shape' => '__listOf__string', 'locationName' => 'principals'], 'StatementId' => ['shape' => '__string', 'locationName' => 'statementId']], 'required' => ['Principals', 'Actions']], 'ApplicationSummary' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'Author' => ['shape' => '__string', 'locationName' => 'author'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'HomePageUrl' => ['shape' => '__string', 'locationName' => 'homePageUrl'], 'Labels' => ['shape' => '__listOf__string', 'locationName' => 'labels'], 'Name' => ['shape' => '__string', 'locationName' => 'name'], 'SpdxLicenseId' => ['shape' => '__string', 'locationName' => 'spdxLicenseId']], 'required' => ['Description', 'Author', 'ApplicationId', 'Name']], 'ApplicationVersionPage' => ['type' => 'structure', 'members' => ['NextToken' => ['shape' => '__string', 'locationName' => 'nextToken'], 'Versions' => ['shape' => '__listOfVersionSummary', 'locationName' => 'versions']], 'required' => ['Versions']], 'BadRequestException' => ['type' => 'structure', 'members' => ['ErrorCode' => ['shape' => '__string', 'locationName' => 'errorCode'], 'Message' => ['shape' => '__string', 'locationName' => 'message']], 'exception' => \true, 'error' => ['httpStatusCode' => 400]], 'Capability' => ['type' => 'string', 'enum' => ['CAPABILITY_IAM', 'CAPABILITY_NAMED_IAM', 'CAPABILITY_AUTO_EXPAND', 'CAPABILITY_RESOURCE_POLICY']], 'ChangeSetDetails' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'ChangeSetId' => ['shape' => '__string', 'locationName' => 'changeSetId'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'StackId' => ['shape' => '__string', 'locationName' => 'stackId']], 'required' => ['ChangeSetId', 'ApplicationId', 'StackId', 'SemanticVersion']], 'ConflictException' => ['type' => 'structure', 'members' => ['ErrorCode' => ['shape' => '__string', 'locationName' => 'errorCode'], 'Message' => ['shape' => '__string', 'locationName' => 'message']], 'exception' => \true, 'error' => ['httpStatusCode' => 409]], 'CreateApplicationInput' => ['type' => 'structure', 'members' => ['Author' => ['shape' => '__string', 'locationName' => 'author'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'HomePageUrl' => ['shape' => '__string', 'locationName' => 'homePageUrl'], 'Labels' => ['shape' => '__listOf__string', 'locationName' => 'labels'], 'LicenseBody' => ['shape' => '__string', 'locationName' => 'licenseBody'], 'LicenseUrl' => ['shape' => '__string', 'locationName' => 'licenseUrl'], 'Name' => ['shape' => '__string', 'locationName' => 'name'], 'ReadmeBody' => ['shape' => '__string', 'locationName' => 'readmeBody'], 'ReadmeUrl' => ['shape' => '__string', 'locationName' => 'readmeUrl'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'SourceCodeArchiveUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeArchiveUrl'], 'SourceCodeUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeUrl'], 'SpdxLicenseId' => ['shape' => '__string', 'locationName' => 'spdxLicenseId'], 'TemplateBody' => ['shape' => '__string', 'locationName' => 'templateBody'], 'TemplateUrl' => ['shape' => '__string', 'locationName' => 'templateUrl']], 'required' => ['Description', 'Name', 'Author']], 'CreateApplicationRequest' => ['type' => 'structure', 'members' => ['Author' => ['shape' => '__string', 'locationName' => 'author'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'HomePageUrl' => ['shape' => '__string', 'locationName' => 'homePageUrl'], 'Labels' => ['shape' => '__listOf__string', 'locationName' => 'labels'], 'LicenseBody' => ['shape' => '__string', 'locationName' => 'licenseBody'], 'LicenseUrl' => ['shape' => '__string', 'locationName' => 'licenseUrl'], 'Name' => ['shape' => '__string', 'locationName' => 'name'], 'ReadmeBody' => ['shape' => '__string', 'locationName' => 'readmeBody'], 'ReadmeUrl' => ['shape' => '__string', 'locationName' => 'readmeUrl'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'SourceCodeArchiveUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeArchiveUrl'], 'SourceCodeUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeUrl'], 'SpdxLicenseId' => ['shape' => '__string', 'locationName' => 'spdxLicenseId'], 'TemplateBody' => ['shape' => '__string', 'locationName' => 'templateBody'], 'TemplateUrl' => ['shape' => '__string', 'locationName' => 'templateUrl']], 'required' => ['Description', 'Name', 'Author']], 'CreateApplicationResponse' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'Author' => ['shape' => '__string', 'locationName' => 'author'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'HomePageUrl' => ['shape' => '__string', 'locationName' => 'homePageUrl'], 'IsVerifiedAuthor' => ['shape' => '__boolean', 'locationName' => 'isVerifiedAuthor'], 'Labels' => ['shape' => '__listOf__string', 'locationName' => 'labels'], 'LicenseUrl' => ['shape' => '__string', 'locationName' => 'licenseUrl'], 'Name' => ['shape' => '__string', 'locationName' => 'name'], 'ReadmeUrl' => ['shape' => '__string', 'locationName' => 'readmeUrl'], 'SpdxLicenseId' => ['shape' => '__string', 'locationName' => 'spdxLicenseId'], 'VerifiedAuthorUrl' => ['shape' => '__string', 'locationName' => 'verifiedAuthorUrl'], 'Version' => ['shape' => 'Version', 'locationName' => 'version']]], 'CreateApplicationVersionInput' => ['type' => 'structure', 'members' => ['SourceCodeArchiveUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeArchiveUrl'], 'SourceCodeUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeUrl'], 'TemplateBody' => ['shape' => '__string', 'locationName' => 'templateBody'], 'TemplateUrl' => ['shape' => '__string', 'locationName' => 'templateUrl']]], 'CreateApplicationVersionRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId'], 'SemanticVersion' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'semanticVersion'], 'SourceCodeArchiveUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeArchiveUrl'], 'SourceCodeUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeUrl'], 'TemplateBody' => ['shape' => '__string', 'locationName' => 'templateBody'], 'TemplateUrl' => ['shape' => '__string', 'locationName' => 'templateUrl']], 'required' => ['ApplicationId', 'SemanticVersion']], 'CreateApplicationVersionResponse' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'ParameterDefinitions' => ['shape' => '__listOfParameterDefinition', 'locationName' => 'parameterDefinitions'], 'RequiredCapabilities' => ['shape' => '__listOfCapability', 'locationName' => 'requiredCapabilities'], 'ResourcesSupported' => ['shape' => '__boolean', 'locationName' => 'resourcesSupported'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'SourceCodeArchiveUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeArchiveUrl'], 'SourceCodeUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeUrl'], 'TemplateUrl' => ['shape' => '__string', 'locationName' => 'templateUrl']]], 'CreateCloudFormationChangeSetInput' => ['type' => 'structure', 'members' => ['Capabilities' => ['shape' => '__listOf__string', 'locationName' => 'capabilities'], 'ChangeSetName' => ['shape' => '__string', 'locationName' => 'changeSetName'], 'ClientToken' => ['shape' => '__string', 'locationName' => 'clientToken'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'NotificationArns' => ['shape' => '__listOf__string', 'locationName' => 'notificationArns'], 'ParameterOverrides' => ['shape' => '__listOfParameterValue', 'locationName' => 'parameterOverrides'], 'ResourceTypes' => ['shape' => '__listOf__string', 'locationName' => 'resourceTypes'], 'RollbackConfiguration' => ['shape' => 'RollbackConfiguration', 'locationName' => 'rollbackConfiguration'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'StackName' => ['shape' => '__string', 'locationName' => 'stackName'], 'Tags' => ['shape' => '__listOfTag', 'locationName' => 'tags'], 'TemplateId' => ['shape' => '__string', 'locationName' => 'templateId']], 'required' => ['StackName']], 'CreateCloudFormationChangeSetRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId'], 'Capabilities' => ['shape' => '__listOf__string', 'locationName' => 'capabilities'], 'ChangeSetName' => ['shape' => '__string', 'locationName' => 'changeSetName'], 'ClientToken' => ['shape' => '__string', 'locationName' => 'clientToken'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'NotificationArns' => ['shape' => '__listOf__string', 'locationName' => 'notificationArns'], 'ParameterOverrides' => ['shape' => '__listOfParameterValue', 'locationName' => 'parameterOverrides'], 'ResourceTypes' => ['shape' => '__listOf__string', 'locationName' => 'resourceTypes'], 'RollbackConfiguration' => ['shape' => 'RollbackConfiguration', 'locationName' => 'rollbackConfiguration'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'StackName' => ['shape' => '__string', 'locationName' => 'stackName'], 'Tags' => ['shape' => '__listOfTag', 'locationName' => 'tags'], 'TemplateId' => ['shape' => '__string', 'locationName' => 'templateId']], 'required' => ['ApplicationId', 'StackName']], 'CreateCloudFormationChangeSetResponse' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'ChangeSetId' => ['shape' => '__string', 'locationName' => 'changeSetId'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'StackId' => ['shape' => '__string', 'locationName' => 'stackId']]], 'CreateCloudFormationTemplateRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion']], 'required' => ['ApplicationId']], 'CreateCloudFormationTemplateResponse' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'ExpirationTime' => ['shape' => '__string', 'locationName' => 'expirationTime'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'Status' => ['shape' => 'Status', 'locationName' => 'status'], 'TemplateId' => ['shape' => '__string', 'locationName' => 'templateId'], 'TemplateUrl' => ['shape' => '__string', 'locationName' => 'templateUrl']]], 'DeleteApplicationRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId']], 'required' => ['ApplicationId']], 'ForbiddenException' => ['type' => 'structure', 'members' => ['ErrorCode' => ['shape' => '__string', 'locationName' => 'errorCode'], 'Message' => ['shape' => '__string', 'locationName' => 'message']], 'exception' => \true, 'error' => ['httpStatusCode' => 403]], 'GetApplicationPolicyRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId']], 'required' => ['ApplicationId']], 'GetApplicationPolicyResponse' => ['type' => 'structure', 'members' => ['Statements' => ['shape' => '__listOfApplicationPolicyStatement', 'locationName' => 'statements']]], 'GetApplicationRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId'], 'SemanticVersion' => ['shape' => '__string', 'location' => 'querystring', 'locationName' => 'semanticVersion']], 'required' => ['ApplicationId']], 'GetApplicationResponse' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'Author' => ['shape' => '__string', 'locationName' => 'author'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'HomePageUrl' => ['shape' => '__string', 'locationName' => 'homePageUrl'], 'IsVerifiedAuthor' => ['shape' => '__boolean', 'locationName' => 'isVerifiedAuthor'], 'Labels' => ['shape' => '__listOf__string', 'locationName' => 'labels'], 'LicenseUrl' => ['shape' => '__string', 'locationName' => 'licenseUrl'], 'Name' => ['shape' => '__string', 'locationName' => 'name'], 'ReadmeUrl' => ['shape' => '__string', 'locationName' => 'readmeUrl'], 'SpdxLicenseId' => ['shape' => '__string', 'locationName' => 'spdxLicenseId'], 'VerifiedAuthorUrl' => ['shape' => '__string', 'locationName' => 'verifiedAuthorUrl'], 'Version' => ['shape' => 'Version', 'locationName' => 'version']]], 'GetCloudFormationTemplateRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId'], 'TemplateId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'templateId']], 'required' => ['ApplicationId', 'TemplateId']], 'GetCloudFormationTemplateResponse' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'ExpirationTime' => ['shape' => '__string', 'locationName' => 'expirationTime'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'Status' => ['shape' => 'Status', 'locationName' => 'status'], 'TemplateId' => ['shape' => '__string', 'locationName' => 'templateId'], 'TemplateUrl' => ['shape' => '__string', 'locationName' => 'templateUrl']]], 'InternalServerErrorException' => ['type' => 'structure', 'members' => ['ErrorCode' => ['shape' => '__string', 'locationName' => 'errorCode'], 'Message' => ['shape' => '__string', 'locationName' => 'message']], 'exception' => \true, 'error' => ['httpStatusCode' => 500]], 'ListApplicationDependenciesRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId'], 'MaxItems' => ['shape' => 'MaxItems', 'location' => 'querystring', 'locationName' => 'maxItems'], 'NextToken' => ['shape' => '__string', 'location' => 'querystring', 'locationName' => 'nextToken'], 'SemanticVersion' => ['shape' => '__string', 'location' => 'querystring', 'locationName' => 'semanticVersion']], 'required' => ['ApplicationId']], 'ListApplicationDependenciesResponse' => ['type' => 'structure', 'members' => ['Dependencies' => ['shape' => '__listOfApplicationDependencySummary', 'locationName' => 'dependencies'], 'NextToken' => ['shape' => '__string', 'locationName' => 'nextToken']]], 'ListApplicationVersionsRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId'], 'MaxItems' => ['shape' => 'MaxItems', 'location' => 'querystring', 'locationName' => 'maxItems'], 'NextToken' => ['shape' => '__string', 'location' => 'querystring', 'locationName' => 'nextToken']], 'required' => ['ApplicationId']], 'ListApplicationVersionsResponse' => ['type' => 'structure', 'members' => ['NextToken' => ['shape' => '__string', 'locationName' => 'nextToken'], 'Versions' => ['shape' => '__listOfVersionSummary', 'locationName' => 'versions']]], 'ListApplicationsRequest' => ['type' => 'structure', 'members' => ['MaxItems' => ['shape' => 'MaxItems', 'location' => 'querystring', 'locationName' => 'maxItems'], 'NextToken' => ['shape' => '__string', 'location' => 'querystring', 'locationName' => 'nextToken']]], 'ListApplicationsResponse' => ['type' => 'structure', 'members' => ['Applications' => ['shape' => '__listOfApplicationSummary', 'locationName' => 'applications'], 'NextToken' => ['shape' => '__string', 'locationName' => 'nextToken']]], 'MaxItems' => ['type' => 'integer', 'min' => 1, 'max' => 100], 'NotFoundException' => ['type' => 'structure', 'members' => ['ErrorCode' => ['shape' => '__string', 'locationName' => 'errorCode'], 'Message' => ['shape' => '__string', 'locationName' => 'message']], 'exception' => \true, 'error' => ['httpStatusCode' => 404]], 'ParameterDefinition' => ['type' => 'structure', 'members' => ['AllowedPattern' => ['shape' => '__string', 'locationName' => 'allowedPattern'], 'AllowedValues' => ['shape' => '__listOf__string', 'locationName' => 'allowedValues'], 'ConstraintDescription' => ['shape' => '__string', 'locationName' => 'constraintDescription'], 'DefaultValue' => ['shape' => '__string', 'locationName' => 'defaultValue'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'MaxLength' => ['shape' => '__integer', 'locationName' => 'maxLength'], 'MaxValue' => ['shape' => '__integer', 'locationName' => 'maxValue'], 'MinLength' => ['shape' => '__integer', 'locationName' => 'minLength'], 'MinValue' => ['shape' => '__integer', 'locationName' => 'minValue'], 'Name' => ['shape' => '__string', 'locationName' => 'name'], 'NoEcho' => ['shape' => '__boolean', 'locationName' => 'noEcho'], 'ReferencedByResources' => ['shape' => '__listOf__string', 'locationName' => 'referencedByResources'], 'Type' => ['shape' => '__string', 'locationName' => 'type']], 'required' => ['ReferencedByResources', 'Name']], 'ParameterValue' => ['type' => 'structure', 'members' => ['Name' => ['shape' => '__string', 'locationName' => 'name'], 'Value' => ['shape' => '__string', 'locationName' => 'value']], 'required' => ['Value', 'Name']], 'PutApplicationPolicyRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId'], 'Statements' => ['shape' => '__listOfApplicationPolicyStatement', 'locationName' => 'statements']], 'required' => ['ApplicationId', 'Statements']], 'PutApplicationPolicyResponse' => ['type' => 'structure', 'members' => ['Statements' => ['shape' => '__listOfApplicationPolicyStatement', 'locationName' => 'statements']]], 'RollbackConfiguration' => ['type' => 'structure', 'members' => ['MonitoringTimeInMinutes' => ['shape' => '__integer', 'locationName' => 'monitoringTimeInMinutes'], 'RollbackTriggers' => ['shape' => '__listOfRollbackTrigger', 'locationName' => 'rollbackTriggers']]], 'RollbackTrigger' => ['type' => 'structure', 'members' => ['Arn' => ['shape' => '__string', 'locationName' => 'arn'], 'Type' => ['shape' => '__string', 'locationName' => 'type']], 'required' => ['Type', 'Arn']], 'Status' => ['type' => 'string', 'enum' => ['PREPARING', 'ACTIVE', 'EXPIRED']], 'Tag' => ['type' => 'structure', 'members' => ['Key' => ['shape' => '__string', 'locationName' => 'key'], 'Value' => ['shape' => '__string', 'locationName' => 'value']], 'required' => ['Value', 'Key']], 'TemplateDetails' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'ExpirationTime' => ['shape' => '__string', 'locationName' => 'expirationTime'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'Status' => ['shape' => 'Status', 'locationName' => 'status'], 'TemplateId' => ['shape' => '__string', 'locationName' => 'templateId'], 'TemplateUrl' => ['shape' => '__string', 'locationName' => 'templateUrl']], 'required' => ['Status', 'TemplateUrl', 'CreationTime', 'ExpirationTime', 'ApplicationId', 'TemplateId', 'SemanticVersion']], 'TooManyRequestsException' => ['type' => 'structure', 'members' => ['ErrorCode' => ['shape' => '__string', 'locationName' => 'errorCode'], 'Message' => ['shape' => '__string', 'locationName' => 'message']], 'exception' => \true, 'error' => ['httpStatusCode' => 429]], 'UnshareApplicationInput' => ['type' => 'structure', 'members' => ['OrganizationId' => ['shape' => '__string', 'locationName' => 'organizationId']], 'required' => ['OrganizationId']], 'UnshareApplicationRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId'], 'OrganizationId' => ['shape' => '__string', 'locationName' => 'organizationId']], 'required' => ['ApplicationId', 'OrganizationId']], 'UpdateApplicationInput' => ['type' => 'structure', 'members' => ['Author' => ['shape' => '__string', 'locationName' => 'author'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'HomePageUrl' => ['shape' => '__string', 'locationName' => 'homePageUrl'], 'Labels' => ['shape' => '__listOf__string', 'locationName' => 'labels'], 'ReadmeBody' => ['shape' => '__string', 'locationName' => 'readmeBody'], 'ReadmeUrl' => ['shape' => '__string', 'locationName' => 'readmeUrl']]], 'UpdateApplicationRequest' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'location' => 'uri', 'locationName' => 'applicationId'], 'Author' => ['shape' => '__string', 'locationName' => 'author'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'HomePageUrl' => ['shape' => '__string', 'locationName' => 'homePageUrl'], 'Labels' => ['shape' => '__listOf__string', 'locationName' => 'labels'], 'ReadmeBody' => ['shape' => '__string', 'locationName' => 'readmeBody'], 'ReadmeUrl' => ['shape' => '__string', 'locationName' => 'readmeUrl']], 'required' => ['ApplicationId']], 'UpdateApplicationResponse' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'Author' => ['shape' => '__string', 'locationName' => 'author'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'Description' => ['shape' => '__string', 'locationName' => 'description'], 'HomePageUrl' => ['shape' => '__string', 'locationName' => 'homePageUrl'], 'IsVerifiedAuthor' => ['shape' => '__boolean', 'locationName' => 'isVerifiedAuthor'], 'Labels' => ['shape' => '__listOf__string', 'locationName' => 'labels'], 'LicenseUrl' => ['shape' => '__string', 'locationName' => 'licenseUrl'], 'Name' => ['shape' => '__string', 'locationName' => 'name'], 'ReadmeUrl' => ['shape' => '__string', 'locationName' => 'readmeUrl'], 'SpdxLicenseId' => ['shape' => '__string', 'locationName' => 'spdxLicenseId'], 'VerifiedAuthorUrl' => ['shape' => '__string', 'locationName' => 'verifiedAuthorUrl'], 'Version' => ['shape' => 'Version', 'locationName' => 'version']]], 'Version' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'ParameterDefinitions' => ['shape' => '__listOfParameterDefinition', 'locationName' => 'parameterDefinitions'], 'RequiredCapabilities' => ['shape' => '__listOfCapability', 'locationName' => 'requiredCapabilities'], 'ResourcesSupported' => ['shape' => '__boolean', 'locationName' => 'resourcesSupported'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'SourceCodeArchiveUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeArchiveUrl'], 'SourceCodeUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeUrl'], 'TemplateUrl' => ['shape' => '__string', 'locationName' => 'templateUrl']], 'required' => ['TemplateUrl', 'ParameterDefinitions', 'ResourcesSupported', 'CreationTime', 'RequiredCapabilities', 'ApplicationId', 'SemanticVersion']], 'VersionSummary' => ['type' => 'structure', 'members' => ['ApplicationId' => ['shape' => '__string', 'locationName' => 'applicationId'], 'CreationTime' => ['shape' => '__string', 'locationName' => 'creationTime'], 'SemanticVersion' => ['shape' => '__string', 'locationName' => 'semanticVersion'], 'SourceCodeUrl' => ['shape' => '__string', 'locationName' => 'sourceCodeUrl']], 'required' => ['CreationTime', 'ApplicationId', 'SemanticVersion']], '__boolean' => ['type' => 'boolean'], '__double' => ['type' => 'double'], '__integer' => ['type' => 'integer'], '__listOfApplicationDependencySummary' => ['type' => 'list', 'member' => ['shape' => 'ApplicationDependencySummary']], '__listOfApplicationPolicyStatement' => ['type' => 'list', 'member' => ['shape' => 'ApplicationPolicyStatement']], '__listOfApplicationSummary' => ['type' => 'list', 'member' => ['shape' => 'ApplicationSummary']], '__listOfCapability' => ['type' => 'list', 'member' => ['shape' => 'Capability']], '__listOfParameterDefinition' => ['type' => 'list', 'member' => ['shape' => 'ParameterDefinition']], '__listOfParameterValue' => ['type' => 'list', 'member' => ['shape' => 'ParameterValue']], '__listOfRollbackTrigger' => ['type' => 'list', 'member' => ['shape' => 'RollbackTrigger']], '__listOfTag' => ['type' => 'list', 'member' => ['shape' => 'Tag']], '__listOfVersionSummary' => ['type' => 'list', 'member' => ['shape' => 'VersionSummary']], '__listOf__string' => ['type' => 'list', 'member' => ['shape' => '__string']], '__long' => ['type' => 'long'], '__string' => ['type' => 'string']]];

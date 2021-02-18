<?php

namespace WPCOM_VIP;

// This file was auto-generated from sdk-root/src/data/application-insights/2018-11-25/api-2.json
return ['version' => '2.0', 'metadata' => ['apiVersion' => '2018-11-25', 'endpointPrefix' => 'applicationinsights', 'jsonVersion' => '1.1', 'protocol' => 'json', 'serviceAbbreviation' => 'Application Insights', 'serviceFullName' => 'Amazon CloudWatch Application Insights', 'serviceId' => 'Application Insights', 'signatureVersion' => 'v4', 'signingName' => 'applicationinsights', 'targetPrefix' => 'EC2WindowsBarleyService', 'uid' => 'application-insights-2018-11-25'], 'operations' => ['CreateApplication' => ['name' => 'CreateApplication', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateApplicationRequest'], 'output' => ['shape' => 'CreateApplicationResponse'], 'errors' => [['shape' => 'ResourceInUseException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException'], ['shape' => 'TagsAlreadyExistException']]], 'CreateComponent' => ['name' => 'CreateComponent', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateComponentRequest'], 'output' => ['shape' => 'CreateComponentResponse'], 'errors' => [['shape' => 'ResourceInUseException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'CreateLogPattern' => ['name' => 'CreateLogPattern', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateLogPatternRequest'], 'output' => ['shape' => 'CreateLogPatternResponse'], 'errors' => [['shape' => 'ResourceInUseException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'DeleteApplication' => ['name' => 'DeleteApplication', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteApplicationRequest'], 'output' => ['shape' => 'DeleteApplicationResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerException']]], 'DeleteComponent' => ['name' => 'DeleteComponent', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteComponentRequest'], 'output' => ['shape' => 'DeleteComponentResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'DeleteLogPattern' => ['name' => 'DeleteLogPattern', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteLogPatternRequest'], 'output' => ['shape' => 'DeleteLogPatternResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'BadRequestException'], ['shape' => 'InternalServerException']]], 'DescribeApplication' => ['name' => 'DescribeApplication', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeApplicationRequest'], 'output' => ['shape' => 'DescribeApplicationResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'DescribeComponent' => ['name' => 'DescribeComponent', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeComponentRequest'], 'output' => ['shape' => 'DescribeComponentResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'DescribeComponentConfiguration' => ['name' => 'DescribeComponentConfiguration', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeComponentConfigurationRequest'], 'output' => ['shape' => 'DescribeComponentConfigurationResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'DescribeComponentConfigurationRecommendation' => ['name' => 'DescribeComponentConfigurationRecommendation', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeComponentConfigurationRecommendationRequest'], 'output' => ['shape' => 'DescribeComponentConfigurationRecommendationResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'DescribeLogPattern' => ['name' => 'DescribeLogPattern', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeLogPatternRequest'], 'output' => ['shape' => 'DescribeLogPatternResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'DescribeObservation' => ['name' => 'DescribeObservation', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeObservationRequest'], 'output' => ['shape' => 'DescribeObservationResponse'], 'errors' => [['shape' => 'InternalServerException'], ['shape' => 'ValidationException'], ['shape' => 'ResourceNotFoundException']]], 'DescribeProblem' => ['name' => 'DescribeProblem', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeProblemRequest'], 'output' => ['shape' => 'DescribeProblemResponse'], 'errors' => [['shape' => 'InternalServerException'], ['shape' => 'ValidationException'], ['shape' => 'ResourceNotFoundException']]], 'DescribeProblemObservations' => ['name' => 'DescribeProblemObservations', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DescribeProblemObservationsRequest'], 'output' => ['shape' => 'DescribeProblemObservationsResponse'], 'errors' => [['shape' => 'InternalServerException'], ['shape' => 'ValidationException'], ['shape' => 'ResourceNotFoundException']]], 'ListApplications' => ['name' => 'ListApplications', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListApplicationsRequest'], 'output' => ['shape' => 'ListApplicationsResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'ListComponents' => ['name' => 'ListComponents', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListComponentsRequest'], 'output' => ['shape' => 'ListComponentsResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'ListConfigurationHistory' => ['name' => 'ListConfigurationHistory', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListConfigurationHistoryRequest'], 'output' => ['shape' => 'ListConfigurationHistoryResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'InternalServerException']]], 'ListLogPatternSets' => ['name' => 'ListLogPatternSets', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListLogPatternSetsRequest'], 'output' => ['shape' => 'ListLogPatternSetsResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'ListLogPatterns' => ['name' => 'ListLogPatterns', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListLogPatternsRequest'], 'output' => ['shape' => 'ListLogPatternsResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'ListProblems' => ['name' => 'ListProblems', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListProblemsRequest'], 'output' => ['shape' => 'ListProblemsResponse'], 'errors' => [['shape' => 'ValidationException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'InternalServerException']]], 'ListTagsForResource' => ['name' => 'ListTagsForResource', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListTagsForResourceRequest'], 'output' => ['shape' => 'ListTagsForResourceResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException']]], 'TagResource' => ['name' => 'TagResource', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'TagResourceRequest'], 'output' => ['shape' => 'TagResourceResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'TooManyTagsException'], ['shape' => 'ValidationException']]], 'UntagResource' => ['name' => 'UntagResource', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UntagResourceRequest'], 'output' => ['shape' => 'UntagResourceResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException']]], 'UpdateApplication' => ['name' => 'UpdateApplication', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UpdateApplicationRequest'], 'output' => ['shape' => 'UpdateApplicationResponse'], 'errors' => [['shape' => 'InternalServerException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException']]], 'UpdateComponent' => ['name' => 'UpdateComponent', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UpdateComponentRequest'], 'output' => ['shape' => 'UpdateComponentResponse'], 'errors' => [['shape' => 'ResourceInUseException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'UpdateComponentConfiguration' => ['name' => 'UpdateComponentConfiguration', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UpdateComponentConfigurationRequest'], 'output' => ['shape' => 'UpdateComponentConfigurationResponse'], 'errors' => [['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]], 'UpdateLogPattern' => ['name' => 'UpdateLogPattern', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UpdateLogPatternRequest'], 'output' => ['shape' => 'UpdateLogPatternResponse'], 'errors' => [['shape' => 'ResourceInUseException'], ['shape' => 'ResourceNotFoundException'], ['shape' => 'ValidationException'], ['shape' => 'InternalServerException']]]], 'shapes' => ['AffectedResource' => ['type' => 'string'], 'AmazonResourceName' => ['type' => 'string', 'max' => 1011, 'min' => 1], 'ApplicationComponent' => ['type' => 'structure', 'members' => ['ComponentName' => ['shape' => 'ComponentName'], 'ResourceType' => ['shape' => 'ResourceType'], 'Tier' => ['shape' => 'Tier'], 'Monitor' => ['shape' => 'Monitor']]], 'ApplicationComponentList' => ['type' => 'list', 'member' => ['shape' => 'ApplicationComponent']], 'ApplicationInfo' => ['type' => 'structure', 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'LifeCycle' => ['shape' => 'LifeCycle'], 'OpsItemSNSTopicArn' => ['shape' => 'OpsItemSNSTopicArn'], 'OpsCenterEnabled' => ['shape' => 'OpsCenterEnabled'], 'CWEMonitorEnabled' => ['shape' => 'CWEMonitorEnabled'], 'Remarks' => ['shape' => 'Remarks']]], 'ApplicationInfoList' => ['type' => 'list', 'member' => ['shape' => 'ApplicationInfo']], 'BadRequestException' => ['type' => 'structure', 'members' => ['Message' => ['shape' => 'ErrorMsg']], 'exception' => \true], 'CWEMonitorEnabled' => ['type' => 'boolean'], 'CloudWatchEventDetailType' => ['type' => 'string'], 'CloudWatchEventId' => ['type' => 'string'], 'CloudWatchEventSource' => ['type' => 'string', 'enum' => ['EC2', 'CODE_DEPLOY', 'HEALTH']], 'CodeDeployApplication' => ['type' => 'string'], 'CodeDeployDeploymentGroup' => ['type' => 'string'], 'CodeDeployDeploymentId' => ['type' => 'string'], 'CodeDeployInstanceGroupId' => ['type' => 'string'], 'CodeDeployState' => ['type' => 'string'], 'ComponentConfiguration' => ['type' => 'string', 'max' => 10000, 'min' => 1], 'ComponentName' => ['type' => 'string'], 'ConfigurationEvent' => ['type' => 'structure', 'members' => ['MonitoredResourceARN' => ['shape' => 'ConfigurationEventMonitoredResourceARN'], 'EventStatus' => ['shape' => 'ConfigurationEventStatus'], 'EventResourceType' => ['shape' => 'ConfigurationEventResourceType'], 'EventTime' => ['shape' => 'ConfigurationEventTime'], 'EventDetail' => ['shape' => 'ConfigurationEventDetail'], 'EventResourceName' => ['shape' => 'ConfigurationEventResourceName']]], 'ConfigurationEventDetail' => ['type' => 'string'], 'ConfigurationEventList' => ['type' => 'list', 'member' => ['shape' => 'ConfigurationEvent']], 'ConfigurationEventMonitoredResourceARN' => ['type' => 'string'], 'ConfigurationEventResourceName' => ['type' => 'string'], 'ConfigurationEventResourceType' => ['type' => 'string', 'enum' => ['CLOUDWATCH_ALARM', 'CLOUDFORMATION', 'SSM_ASSOCIATION']], 'ConfigurationEventStatus' => ['type' => 'string', 'enum' => ['INFO', 'WARN', 'ERROR']], 'ConfigurationEventTime' => ['type' => 'timestamp'], 'CreateApplicationRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'OpsCenterEnabled' => ['shape' => 'OpsCenterEnabled'], 'CWEMonitorEnabled' => ['shape' => 'CWEMonitorEnabled'], 'OpsItemSNSTopicArn' => ['shape' => 'OpsItemSNSTopicArn'], 'Tags' => ['shape' => 'TagList']]], 'CreateApplicationResponse' => ['type' => 'structure', 'members' => ['ApplicationInfo' => ['shape' => 'ApplicationInfo']]], 'CreateComponentRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'ComponentName', 'ResourceList'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'ComponentName' => ['shape' => 'ComponentName'], 'ResourceList' => ['shape' => 'ResourceList']]], 'CreateComponentResponse' => ['type' => 'structure', 'members' => []], 'CreateLogPatternRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'PatternSetName', 'PatternName', 'Pattern', 'Rank'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'PatternSetName' => ['shape' => 'LogPatternSetName'], 'PatternName' => ['shape' => 'LogPatternName'], 'Pattern' => ['shape' => 'LogPatternRegex'], 'Rank' => ['shape' => 'LogPatternRank']]], 'CreateLogPatternResponse' => ['type' => 'structure', 'members' => ['LogPattern' => ['shape' => 'LogPattern'], 'ResourceGroupName' => ['shape' => 'ResourceGroupName']]], 'DeleteApplicationRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName']]], 'DeleteApplicationResponse' => ['type' => 'structure', 'members' => []], 'DeleteComponentRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'ComponentName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'ComponentName' => ['shape' => 'ComponentName']]], 'DeleteComponentResponse' => ['type' => 'structure', 'members' => []], 'DeleteLogPatternRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'PatternSetName', 'PatternName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'PatternSetName' => ['shape' => 'LogPatternSetName'], 'PatternName' => ['shape' => 'LogPatternName']]], 'DeleteLogPatternResponse' => ['type' => 'structure', 'members' => []], 'DescribeApplicationRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName']]], 'DescribeApplicationResponse' => ['type' => 'structure', 'members' => ['ApplicationInfo' => ['shape' => 'ApplicationInfo']]], 'DescribeComponentConfigurationRecommendationRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'ComponentName', 'Tier'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'ComponentName' => ['shape' => 'ComponentName'], 'Tier' => ['shape' => 'Tier']]], 'DescribeComponentConfigurationRecommendationResponse' => ['type' => 'structure', 'members' => ['ComponentConfiguration' => ['shape' => 'ComponentConfiguration']]], 'DescribeComponentConfigurationRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'ComponentName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'ComponentName' => ['shape' => 'ComponentName']]], 'DescribeComponentConfigurationResponse' => ['type' => 'structure', 'members' => ['Monitor' => ['shape' => 'Monitor'], 'Tier' => ['shape' => 'Tier'], 'ComponentConfiguration' => ['shape' => 'ComponentConfiguration']]], 'DescribeComponentRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'ComponentName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'ComponentName' => ['shape' => 'ComponentName']]], 'DescribeComponentResponse' => ['type' => 'structure', 'members' => ['ApplicationComponent' => ['shape' => 'ApplicationComponent'], 'ResourceList' => ['shape' => 'ResourceList']]], 'DescribeLogPatternRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'PatternSetName', 'PatternName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'PatternSetName' => ['shape' => 'LogPatternSetName'], 'PatternName' => ['shape' => 'LogPatternName']]], 'DescribeLogPatternResponse' => ['type' => 'structure', 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'LogPattern' => ['shape' => 'LogPattern']]], 'DescribeObservationRequest' => ['type' => 'structure', 'required' => ['ObservationId'], 'members' => ['ObservationId' => ['shape' => 'ObservationId']]], 'DescribeObservationResponse' => ['type' => 'structure', 'members' => ['Observation' => ['shape' => 'Observation']]], 'DescribeProblemObservationsRequest' => ['type' => 'structure', 'required' => ['ProblemId'], 'members' => ['ProblemId' => ['shape' => 'ProblemId']]], 'DescribeProblemObservationsResponse' => ['type' => 'structure', 'members' => ['RelatedObservations' => ['shape' => 'RelatedObservations']]], 'DescribeProblemRequest' => ['type' => 'structure', 'required' => ['ProblemId'], 'members' => ['ProblemId' => ['shape' => 'ProblemId']]], 'DescribeProblemResponse' => ['type' => 'structure', 'members' => ['Problem' => ['shape' => 'Problem']]], 'Ec2State' => ['type' => 'string'], 'EndTime' => ['type' => 'timestamp'], 'ErrorMsg' => ['type' => 'string'], 'ExceptionMessage' => ['type' => 'string'], 'Feedback' => ['type' => 'map', 'key' => ['shape' => 'FeedbackKey'], 'value' => ['shape' => 'FeedbackValue'], 'max' => 10], 'FeedbackKey' => ['type' => 'string', 'enum' => ['INSIGHTS_FEEDBACK']], 'FeedbackValue' => ['type' => 'string', 'enum' => ['NOT_SPECIFIED', 'USEFUL', 'NOT_USEFUL']], 'HealthEventArn' => ['type' => 'string'], 'HealthEventDescription' => ['type' => 'string'], 'HealthEventTypeCategory' => ['type' => 'string'], 'HealthEventTypeCode' => ['type' => 'string'], 'HealthService' => ['type' => 'string'], 'Insights' => ['type' => 'string'], 'InternalServerException' => ['type' => 'structure', 'members' => ['Message' => ['shape' => 'ErrorMsg']], 'exception' => \true], 'LifeCycle' => ['type' => 'string'], 'LineTime' => ['type' => 'timestamp'], 'ListApplicationsRequest' => ['type' => 'structure', 'members' => ['MaxResults' => ['shape' => 'MaxEntities'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListApplicationsResponse' => ['type' => 'structure', 'members' => ['ApplicationInfoList' => ['shape' => 'ApplicationInfoList'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListComponentsRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'MaxResults' => ['shape' => 'MaxEntities'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListComponentsResponse' => ['type' => 'structure', 'members' => ['ApplicationComponentList' => ['shape' => 'ApplicationComponentList'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListConfigurationHistoryRequest' => ['type' => 'structure', 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'StartTime' => ['shape' => 'StartTime'], 'EndTime' => ['shape' => 'EndTime'], 'EventStatus' => ['shape' => 'ConfigurationEventStatus'], 'MaxResults' => ['shape' => 'MaxEntities'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListConfigurationHistoryResponse' => ['type' => 'structure', 'members' => ['EventList' => ['shape' => 'ConfigurationEventList'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListLogPatternSetsRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'MaxResults' => ['shape' => 'MaxEntities'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListLogPatternSetsResponse' => ['type' => 'structure', 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'LogPatternSets' => ['shape' => 'LogPatternSetList'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListLogPatternsRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'PatternSetName' => ['shape' => 'LogPatternSetName'], 'MaxResults' => ['shape' => 'MaxEntities'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListLogPatternsResponse' => ['type' => 'structure', 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'LogPatterns' => ['shape' => 'LogPatternList'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListProblemsRequest' => ['type' => 'structure', 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'StartTime' => ['shape' => 'StartTime'], 'EndTime' => ['shape' => 'EndTime'], 'MaxResults' => ['shape' => 'MaxEntities'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListProblemsResponse' => ['type' => 'structure', 'members' => ['ProblemList' => ['shape' => 'ProblemList'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListTagsForResourceRequest' => ['type' => 'structure', 'required' => ['ResourceARN'], 'members' => ['ResourceARN' => ['shape' => 'AmazonResourceName']]], 'ListTagsForResourceResponse' => ['type' => 'structure', 'members' => ['Tags' => ['shape' => 'TagList']]], 'LogFilter' => ['type' => 'string', 'enum' => ['ERROR', 'WARN', 'INFO']], 'LogGroup' => ['type' => 'string'], 'LogPattern' => ['type' => 'structure', 'members' => ['PatternSetName' => ['shape' => 'LogPatternSetName'], 'PatternName' => ['shape' => 'LogPatternName'], 'Pattern' => ['shape' => 'LogPatternRegex'], 'Rank' => ['shape' => 'LogPatternRank']]], 'LogPatternList' => ['type' => 'list', 'member' => ['shape' => 'LogPattern']], 'LogPatternName' => ['type' => 'string', 'max' => 50, 'min' => 1, 'pattern' => '[a-zA-Z0-9\\.\\-_]*'], 'LogPatternRank' => ['type' => 'integer'], 'LogPatternRegex' => ['type' => 'string', 'max' => 50, 'min' => 1], 'LogPatternSetList' => ['type' => 'list', 'member' => ['shape' => 'LogPatternSetName']], 'LogPatternSetName' => ['type' => 'string', 'max' => 30, 'min' => 1, 'pattern' => '[a-zA-Z0-9\\.\\-_]*'], 'LogText' => ['type' => 'string'], 'MaxEntities' => ['type' => 'integer', 'max' => 40, 'min' => 1], 'MetricName' => ['type' => 'string'], 'MetricNamespace' => ['type' => 'string'], 'Monitor' => ['type' => 'boolean'], 'NewComponentName' => ['type' => 'string'], 'Observation' => ['type' => 'structure', 'members' => ['Id' => ['shape' => 'ObservationId'], 'StartTime' => ['shape' => 'StartTime'], 'EndTime' => ['shape' => 'EndTime'], 'SourceType' => ['shape' => 'SourceType'], 'SourceARN' => ['shape' => 'SourceARN'], 'LogGroup' => ['shape' => 'LogGroup'], 'LineTime' => ['shape' => 'LineTime'], 'LogText' => ['shape' => 'LogText'], 'LogFilter' => ['shape' => 'LogFilter'], 'MetricNamespace' => ['shape' => 'MetricNamespace'], 'MetricName' => ['shape' => 'MetricName'], 'Unit' => ['shape' => 'Unit'], 'Value' => ['shape' => 'Value'], 'CloudWatchEventId' => ['shape' => 'CloudWatchEventId'], 'CloudWatchEventSource' => ['shape' => 'CloudWatchEventSource'], 'CloudWatchEventDetailType' => ['shape' => 'CloudWatchEventDetailType'], 'HealthEventArn' => ['shape' => 'HealthEventArn'], 'HealthService' => ['shape' => 'HealthService'], 'HealthEventTypeCode' => ['shape' => 'HealthEventTypeCode'], 'HealthEventTypeCategory' => ['shape' => 'HealthEventTypeCategory'], 'HealthEventDescription' => ['shape' => 'HealthEventDescription'], 'CodeDeployDeploymentId' => ['shape' => 'CodeDeployDeploymentId'], 'CodeDeployDeploymentGroup' => ['shape' => 'CodeDeployDeploymentGroup'], 'CodeDeployState' => ['shape' => 'CodeDeployState'], 'CodeDeployApplication' => ['shape' => 'CodeDeployApplication'], 'CodeDeployInstanceGroupId' => ['shape' => 'CodeDeployInstanceGroupId'], 'Ec2State' => ['shape' => 'Ec2State'], 'XRayFaultPercent' => ['shape' => 'XRayFaultPercent'], 'XRayThrottlePercent' => ['shape' => 'XRayThrottlePercent'], 'XRayErrorPercent' => ['shape' => 'XRayErrorPercent'], 'XRayRequestCount' => ['shape' => 'XRayRequestCount'], 'XRayRequestAverageLatency' => ['shape' => 'XRayRequestAverageLatency'], 'XRayNodeName' => ['shape' => 'XRayNodeName'], 'XRayNodeType' => ['shape' => 'XRayNodeType']]], 'ObservationId' => ['type' => 'string', 'max' => 38, 'min' => 38, 'pattern' => 'o-[0-9a-fA-F]{8}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{12}'], 'ObservationList' => ['type' => 'list', 'member' => ['shape' => 'Observation']], 'OpsCenterEnabled' => ['type' => 'boolean'], 'OpsItemSNSTopicArn' => ['type' => 'string', 'max' => 300, 'min' => 20], 'PaginationToken' => ['type' => 'string'], 'Problem' => ['type' => 'structure', 'members' => ['Id' => ['shape' => 'ProblemId'], 'Title' => ['shape' => 'Title'], 'Insights' => ['shape' => 'Insights'], 'Status' => ['shape' => 'Status'], 'AffectedResource' => ['shape' => 'AffectedResource'], 'StartTime' => ['shape' => 'StartTime'], 'EndTime' => ['shape' => 'EndTime'], 'SeverityLevel' => ['shape' => 'SeverityLevel'], 'ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'Feedback' => ['shape' => 'Feedback']]], 'ProblemId' => ['type' => 'string', 'max' => 38, 'min' => 38, 'pattern' => 'p-[0-9a-fA-F]{8}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{4}\\-[0-9a-fA-F]{12}'], 'ProblemList' => ['type' => 'list', 'member' => ['shape' => 'Problem']], 'RelatedObservations' => ['type' => 'structure', 'members' => ['ObservationList' => ['shape' => 'ObservationList']]], 'Remarks' => ['type' => 'string'], 'RemoveSNSTopic' => ['type' => 'boolean'], 'ResourceARN' => ['type' => 'string', 'max' => 1011, 'min' => 1], 'ResourceGroupName' => ['type' => 'string', 'max' => 256, 'min' => 1, 'pattern' => '[a-zA-Z0-9\\.\\-_]*'], 'ResourceInUseException' => ['type' => 'structure', 'members' => ['Message' => ['shape' => 'ErrorMsg']], 'exception' => \true], 'ResourceList' => ['type' => 'list', 'member' => ['shape' => 'ResourceARN']], 'ResourceNotFoundException' => ['type' => 'structure', 'members' => ['Message' => ['shape' => 'ErrorMsg']], 'exception' => \true], 'ResourceType' => ['type' => 'string'], 'SeverityLevel' => ['type' => 'string', 'enum' => ['Low', 'Medium', 'High']], 'SourceARN' => ['type' => 'string'], 'SourceType' => ['type' => 'string'], 'StartTime' => ['type' => 'timestamp'], 'Status' => ['type' => 'string', 'enum' => ['IGNORE', 'RESOLVED', 'PENDING']], 'Tag' => ['type' => 'structure', 'required' => ['Key', 'Value'], 'members' => ['Key' => ['shape' => 'TagKey'], 'Value' => ['shape' => 'TagValue']]], 'TagKey' => ['type' => 'string', 'max' => 128, 'min' => 1], 'TagKeyList' => ['type' => 'list', 'member' => ['shape' => 'TagKey'], 'max' => 200, 'min' => 0], 'TagList' => ['type' => 'list', 'member' => ['shape' => 'Tag'], 'max' => 200, 'min' => 0], 'TagResourceRequest' => ['type' => 'structure', 'required' => ['ResourceARN', 'Tags'], 'members' => ['ResourceARN' => ['shape' => 'AmazonResourceName'], 'Tags' => ['shape' => 'TagList']]], 'TagResourceResponse' => ['type' => 'structure', 'members' => []], 'TagValue' => ['type' => 'string', 'max' => 256, 'min' => 0], 'TagsAlreadyExistException' => ['type' => 'structure', 'members' => ['Message' => ['shape' => 'ExceptionMessage']], 'exception' => \true], 'Tier' => ['type' => 'string', 'enum' => ['DEFAULT', 'DOT_NET_CORE', 'DOT_NET_WORKER', 'DOT_NET_WEB', 'SQL_SERVER'], 'max' => 50, 'min' => 1], 'Title' => ['type' => 'string'], 'TooManyTagsException' => ['type' => 'structure', 'members' => ['Message' => ['shape' => 'ExceptionMessage'], 'ResourceName' => ['shape' => 'AmazonResourceName']], 'exception' => \true], 'Unit' => ['type' => 'string'], 'UntagResourceRequest' => ['type' => 'structure', 'required' => ['ResourceARN', 'TagKeys'], 'members' => ['ResourceARN' => ['shape' => 'AmazonResourceName'], 'TagKeys' => ['shape' => 'TagKeyList']]], 'UntagResourceResponse' => ['type' => 'structure', 'members' => []], 'UpdateApplicationRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'OpsCenterEnabled' => ['shape' => 'OpsCenterEnabled'], 'CWEMonitorEnabled' => ['shape' => 'CWEMonitorEnabled'], 'OpsItemSNSTopicArn' => ['shape' => 'OpsItemSNSTopicArn'], 'RemoveSNSTopic' => ['shape' => 'RemoveSNSTopic']]], 'UpdateApplicationResponse' => ['type' => 'structure', 'members' => ['ApplicationInfo' => ['shape' => 'ApplicationInfo']]], 'UpdateComponentConfigurationRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'ComponentName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'ComponentName' => ['shape' => 'ComponentName'], 'Monitor' => ['shape' => 'Monitor'], 'Tier' => ['shape' => 'Tier'], 'ComponentConfiguration' => ['shape' => 'ComponentConfiguration']]], 'UpdateComponentConfigurationResponse' => ['type' => 'structure', 'members' => []], 'UpdateComponentRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'ComponentName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'ComponentName' => ['shape' => 'ComponentName'], 'NewComponentName' => ['shape' => 'NewComponentName'], 'ResourceList' => ['shape' => 'ResourceList']]], 'UpdateComponentResponse' => ['type' => 'structure', 'members' => []], 'UpdateLogPatternRequest' => ['type' => 'structure', 'required' => ['ResourceGroupName', 'PatternSetName', 'PatternName'], 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'PatternSetName' => ['shape' => 'LogPatternSetName'], 'PatternName' => ['shape' => 'LogPatternName'], 'Pattern' => ['shape' => 'LogPatternRegex'], 'Rank' => ['shape' => 'LogPatternRank']]], 'UpdateLogPatternResponse' => ['type' => 'structure', 'members' => ['ResourceGroupName' => ['shape' => 'ResourceGroupName'], 'LogPattern' => ['shape' => 'LogPattern']]], 'ValidationException' => ['type' => 'structure', 'members' => ['Message' => ['shape' => 'ErrorMsg']], 'exception' => \true], 'Value' => ['type' => 'double'], 'XRayErrorPercent' => ['type' => 'integer'], 'XRayFaultPercent' => ['type' => 'integer'], 'XRayNodeName' => ['type' => 'string'], 'XRayNodeType' => ['type' => 'string'], 'XRayRequestAverageLatency' => ['type' => 'long'], 'XRayRequestCount' => ['type' => 'integer'], 'XRayThrottlePercent' => ['type' => 'integer']]];

<?php

namespace WPCOM_VIP;

// This file was auto-generated from sdk-root/src/data/mturk-requester/2017-01-17/api-2.json
return ['version' => '2.0', 'metadata' => ['apiVersion' => '2017-01-17', 'endpointPrefix' => 'mturk-requester', 'jsonVersion' => '1.1', 'protocol' => 'json', 'serviceAbbreviation' => 'Amazon MTurk', 'serviceFullName' => 'Amazon Mechanical Turk', 'serviceId' => 'MTurk', 'signatureVersion' => 'v4', 'targetPrefix' => 'MTurkRequesterServiceV20170117', 'uid' => 'mturk-requester-2017-01-17'], 'operations' => ['AcceptQualificationRequest' => ['name' => 'AcceptQualificationRequest', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'AcceptQualificationRequestRequest'], 'output' => ['shape' => 'AcceptQualificationRequestResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'ApproveAssignment' => ['name' => 'ApproveAssignment', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ApproveAssignmentRequest'], 'output' => ['shape' => 'ApproveAssignmentResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'AssociateQualificationWithWorker' => ['name' => 'AssociateQualificationWithWorker', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'AssociateQualificationWithWorkerRequest'], 'output' => ['shape' => 'AssociateQualificationWithWorkerResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'CreateAdditionalAssignmentsForHIT' => ['name' => 'CreateAdditionalAssignmentsForHIT', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateAdditionalAssignmentsForHITRequest'], 'output' => ['shape' => 'CreateAdditionalAssignmentsForHITResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'CreateHIT' => ['name' => 'CreateHIT', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateHITRequest'], 'output' => ['shape' => 'CreateHITResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'CreateHITType' => ['name' => 'CreateHITType', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateHITTypeRequest'], 'output' => ['shape' => 'CreateHITTypeResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'CreateHITWithHITType' => ['name' => 'CreateHITWithHITType', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateHITWithHITTypeRequest'], 'output' => ['shape' => 'CreateHITWithHITTypeResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'CreateQualificationType' => ['name' => 'CreateQualificationType', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateQualificationTypeRequest'], 'output' => ['shape' => 'CreateQualificationTypeResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'CreateWorkerBlock' => ['name' => 'CreateWorkerBlock', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'CreateWorkerBlockRequest'], 'output' => ['shape' => 'CreateWorkerBlockResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'DeleteHIT' => ['name' => 'DeleteHIT', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteHITRequest'], 'output' => ['shape' => 'DeleteHITResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'DeleteQualificationType' => ['name' => 'DeleteQualificationType', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteQualificationTypeRequest'], 'output' => ['shape' => 'DeleteQualificationTypeResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'DeleteWorkerBlock' => ['name' => 'DeleteWorkerBlock', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DeleteWorkerBlockRequest'], 'output' => ['shape' => 'DeleteWorkerBlockResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'DisassociateQualificationFromWorker' => ['name' => 'DisassociateQualificationFromWorker', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'DisassociateQualificationFromWorkerRequest'], 'output' => ['shape' => 'DisassociateQualificationFromWorkerResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'GetAccountBalance' => ['name' => 'GetAccountBalance', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetAccountBalanceRequest'], 'output' => ['shape' => 'GetAccountBalanceResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'GetAssignment' => ['name' => 'GetAssignment', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetAssignmentRequest'], 'output' => ['shape' => 'GetAssignmentResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'GetFileUploadURL' => ['name' => 'GetFileUploadURL', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetFileUploadURLRequest'], 'output' => ['shape' => 'GetFileUploadURLResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'GetHIT' => ['name' => 'GetHIT', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetHITRequest'], 'output' => ['shape' => 'GetHITResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'GetQualificationScore' => ['name' => 'GetQualificationScore', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetQualificationScoreRequest'], 'output' => ['shape' => 'GetQualificationScoreResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'GetQualificationType' => ['name' => 'GetQualificationType', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'GetQualificationTypeRequest'], 'output' => ['shape' => 'GetQualificationTypeResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'ListAssignmentsForHIT' => ['name' => 'ListAssignmentsForHIT', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListAssignmentsForHITRequest'], 'output' => ['shape' => 'ListAssignmentsForHITResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'ListBonusPayments' => ['name' => 'ListBonusPayments', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListBonusPaymentsRequest'], 'output' => ['shape' => 'ListBonusPaymentsResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'ListHITs' => ['name' => 'ListHITs', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListHITsRequest'], 'output' => ['shape' => 'ListHITsResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'ListHITsForQualificationType' => ['name' => 'ListHITsForQualificationType', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListHITsForQualificationTypeRequest'], 'output' => ['shape' => 'ListHITsForQualificationTypeResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'ListQualificationRequests' => ['name' => 'ListQualificationRequests', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListQualificationRequestsRequest'], 'output' => ['shape' => 'ListQualificationRequestsResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'ListQualificationTypes' => ['name' => 'ListQualificationTypes', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListQualificationTypesRequest'], 'output' => ['shape' => 'ListQualificationTypesResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'ListReviewPolicyResultsForHIT' => ['name' => 'ListReviewPolicyResultsForHIT', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListReviewPolicyResultsForHITRequest'], 'output' => ['shape' => 'ListReviewPolicyResultsForHITResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'ListReviewableHITs' => ['name' => 'ListReviewableHITs', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListReviewableHITsRequest'], 'output' => ['shape' => 'ListReviewableHITsResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'ListWorkerBlocks' => ['name' => 'ListWorkerBlocks', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListWorkerBlocksRequest'], 'output' => ['shape' => 'ListWorkerBlocksResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'ListWorkersWithQualificationType' => ['name' => 'ListWorkersWithQualificationType', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'ListWorkersWithQualificationTypeRequest'], 'output' => ['shape' => 'ListWorkersWithQualificationTypeResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'NotifyWorkers' => ['name' => 'NotifyWorkers', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'NotifyWorkersRequest'], 'output' => ['shape' => 'NotifyWorkersResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'RejectAssignment' => ['name' => 'RejectAssignment', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'RejectAssignmentRequest'], 'output' => ['shape' => 'RejectAssignmentResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'RejectQualificationRequest' => ['name' => 'RejectQualificationRequest', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'RejectQualificationRequestRequest'], 'output' => ['shape' => 'RejectQualificationRequestResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'SendBonus' => ['name' => 'SendBonus', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'SendBonusRequest'], 'output' => ['shape' => 'SendBonusResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'SendTestEventNotification' => ['name' => 'SendTestEventNotification', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'SendTestEventNotificationRequest'], 'output' => ['shape' => 'SendTestEventNotificationResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]], 'UpdateExpirationForHIT' => ['name' => 'UpdateExpirationForHIT', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UpdateExpirationForHITRequest'], 'output' => ['shape' => 'UpdateExpirationForHITResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'UpdateHITReviewStatus' => ['name' => 'UpdateHITReviewStatus', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UpdateHITReviewStatusRequest'], 'output' => ['shape' => 'UpdateHITReviewStatusResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'UpdateHITTypeOfHIT' => ['name' => 'UpdateHITTypeOfHIT', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UpdateHITTypeOfHITRequest'], 'output' => ['shape' => 'UpdateHITTypeOfHITResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'UpdateNotificationSettings' => ['name' => 'UpdateNotificationSettings', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UpdateNotificationSettingsRequest'], 'output' => ['shape' => 'UpdateNotificationSettingsResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']], 'idempotent' => \true], 'UpdateQualificationType' => ['name' => 'UpdateQualificationType', 'http' => ['method' => 'POST', 'requestUri' => '/'], 'input' => ['shape' => 'UpdateQualificationTypeRequest'], 'output' => ['shape' => 'UpdateQualificationTypeResponse'], 'errors' => [['shape' => 'ServiceFault'], ['shape' => 'RequestError']]]], 'shapes' => ['AcceptQualificationRequestRequest' => ['type' => 'structure', 'required' => ['QualificationRequestId'], 'members' => ['QualificationRequestId' => ['shape' => 'String'], 'IntegerValue' => ['shape' => 'Integer']]], 'AcceptQualificationRequestResponse' => ['type' => 'structure', 'members' => []], 'ApproveAssignmentRequest' => ['type' => 'structure', 'required' => ['AssignmentId'], 'members' => ['AssignmentId' => ['shape' => 'EntityId'], 'RequesterFeedback' => ['shape' => 'String'], 'OverrideRejection' => ['shape' => 'Boolean']]], 'ApproveAssignmentResponse' => ['type' => 'structure', 'members' => []], 'Assignment' => ['type' => 'structure', 'members' => ['AssignmentId' => ['shape' => 'EntityId'], 'WorkerId' => ['shape' => 'CustomerId'], 'HITId' => ['shape' => 'EntityId'], 'AssignmentStatus' => ['shape' => 'AssignmentStatus'], 'AutoApprovalTime' => ['shape' => 'Timestamp'], 'AcceptTime' => ['shape' => 'Timestamp'], 'SubmitTime' => ['shape' => 'Timestamp'], 'ApprovalTime' => ['shape' => 'Timestamp'], 'RejectionTime' => ['shape' => 'Timestamp'], 'Deadline' => ['shape' => 'Timestamp'], 'Answer' => ['shape' => 'String'], 'RequesterFeedback' => ['shape' => 'String']]], 'AssignmentList' => ['type' => 'list', 'member' => ['shape' => 'Assignment']], 'AssignmentStatus' => ['type' => 'string', 'enum' => ['Submitted', 'Approved', 'Rejected']], 'AssignmentStatusList' => ['type' => 'list', 'member' => ['shape' => 'AssignmentStatus']], 'AssociateQualificationWithWorkerRequest' => ['type' => 'structure', 'required' => ['QualificationTypeId', 'WorkerId'], 'members' => ['QualificationTypeId' => ['shape' => 'EntityId'], 'WorkerId' => ['shape' => 'CustomerId'], 'IntegerValue' => ['shape' => 'Integer'], 'SendNotification' => ['shape' => 'Boolean']]], 'AssociateQualificationWithWorkerResponse' => ['type' => 'structure', 'members' => []], 'BonusPayment' => ['type' => 'structure', 'members' => ['WorkerId' => ['shape' => 'CustomerId'], 'BonusAmount' => ['shape' => 'CurrencyAmount'], 'AssignmentId' => ['shape' => 'EntityId'], 'Reason' => ['shape' => 'String'], 'GrantTime' => ['shape' => 'Timestamp']]], 'BonusPaymentList' => ['type' => 'list', 'member' => ['shape' => 'BonusPayment']], 'Boolean' => ['type' => 'boolean'], 'Comparator' => ['type' => 'string', 'enum' => ['LessThan', 'LessThanOrEqualTo', 'GreaterThan', 'GreaterThanOrEqualTo', 'EqualTo', 'NotEqualTo', 'Exists', 'DoesNotExist', 'In', 'NotIn']], 'CountryParameters' => ['type' => 'string', 'max' => 2, 'min' => 2], 'CreateAdditionalAssignmentsForHITRequest' => ['type' => 'structure', 'required' => ['HITId', 'NumberOfAdditionalAssignments'], 'members' => ['HITId' => ['shape' => 'EntityId'], 'NumberOfAdditionalAssignments' => ['shape' => 'Integer'], 'UniqueRequestToken' => ['shape' => 'IdempotencyToken']]], 'CreateAdditionalAssignmentsForHITResponse' => ['type' => 'structure', 'members' => []], 'CreateHITRequest' => ['type' => 'structure', 'required' => ['LifetimeInSeconds', 'AssignmentDurationInSeconds', 'Reward', 'Title', 'Description'], 'members' => ['MaxAssignments' => ['shape' => 'Integer'], 'AutoApprovalDelayInSeconds' => ['shape' => 'Long'], 'LifetimeInSeconds' => ['shape' => 'Long'], 'AssignmentDurationInSeconds' => ['shape' => 'Long'], 'Reward' => ['shape' => 'CurrencyAmount'], 'Title' => ['shape' => 'String'], 'Keywords' => ['shape' => 'String'], 'Description' => ['shape' => 'String'], 'Question' => ['shape' => 'String'], 'RequesterAnnotation' => ['shape' => 'String'], 'QualificationRequirements' => ['shape' => 'QualificationRequirementList'], 'UniqueRequestToken' => ['shape' => 'IdempotencyToken'], 'AssignmentReviewPolicy' => ['shape' => 'ReviewPolicy'], 'HITReviewPolicy' => ['shape' => 'ReviewPolicy'], 'HITLayoutId' => ['shape' => 'EntityId'], 'HITLayoutParameters' => ['shape' => 'HITLayoutParameterList']]], 'CreateHITResponse' => ['type' => 'structure', 'members' => ['HIT' => ['shape' => 'HIT']]], 'CreateHITTypeRequest' => ['type' => 'structure', 'required' => ['AssignmentDurationInSeconds', 'Reward', 'Title', 'Description'], 'members' => ['AutoApprovalDelayInSeconds' => ['shape' => 'Long'], 'AssignmentDurationInSeconds' => ['shape' => 'Long'], 'Reward' => ['shape' => 'CurrencyAmount'], 'Title' => ['shape' => 'String'], 'Keywords' => ['shape' => 'String'], 'Description' => ['shape' => 'String'], 'QualificationRequirements' => ['shape' => 'QualificationRequirementList']]], 'CreateHITTypeResponse' => ['type' => 'structure', 'members' => ['HITTypeId' => ['shape' => 'EntityId']]], 'CreateHITWithHITTypeRequest' => ['type' => 'structure', 'required' => ['HITTypeId', 'LifetimeInSeconds'], 'members' => ['HITTypeId' => ['shape' => 'EntityId'], 'MaxAssignments' => ['shape' => 'Integer'], 'LifetimeInSeconds' => ['shape' => 'Long'], 'Question' => ['shape' => 'String'], 'RequesterAnnotation' => ['shape' => 'String'], 'UniqueRequestToken' => ['shape' => 'IdempotencyToken'], 'AssignmentReviewPolicy' => ['shape' => 'ReviewPolicy'], 'HITReviewPolicy' => ['shape' => 'ReviewPolicy'], 'HITLayoutId' => ['shape' => 'EntityId'], 'HITLayoutParameters' => ['shape' => 'HITLayoutParameterList']]], 'CreateHITWithHITTypeResponse' => ['type' => 'structure', 'members' => ['HIT' => ['shape' => 'HIT']]], 'CreateQualificationTypeRequest' => ['type' => 'structure', 'required' => ['Name', 'Description', 'QualificationTypeStatus'], 'members' => ['Name' => ['shape' => 'String'], 'Keywords' => ['shape' => 'String'], 'Description' => ['shape' => 'String'], 'QualificationTypeStatus' => ['shape' => 'QualificationTypeStatus'], 'RetryDelayInSeconds' => ['shape' => 'Long'], 'Test' => ['shape' => 'String'], 'AnswerKey' => ['shape' => 'String'], 'TestDurationInSeconds' => ['shape' => 'Long'], 'AutoGranted' => ['shape' => 'Boolean'], 'AutoGrantedValue' => ['shape' => 'Integer']]], 'CreateQualificationTypeResponse' => ['type' => 'structure', 'members' => ['QualificationType' => ['shape' => 'QualificationType']]], 'CreateWorkerBlockRequest' => ['type' => 'structure', 'required' => ['WorkerId', 'Reason'], 'members' => ['WorkerId' => ['shape' => 'CustomerId'], 'Reason' => ['shape' => 'String']]], 'CreateWorkerBlockResponse' => ['type' => 'structure', 'members' => []], 'CurrencyAmount' => ['type' => 'string', 'pattern' => '^[0-9]+(\\.)?[0-9]{0,2}$'], 'CustomerId' => ['type' => 'string', 'max' => 64, 'min' => 1, 'pattern' => '^A[A-Z0-9]+$'], 'CustomerIdList' => ['type' => 'list', 'member' => ['shape' => 'CustomerId']], 'DeleteHITRequest' => ['type' => 'structure', 'required' => ['HITId'], 'members' => ['HITId' => ['shape' => 'EntityId']]], 'DeleteHITResponse' => ['type' => 'structure', 'members' => []], 'DeleteQualificationTypeRequest' => ['type' => 'structure', 'required' => ['QualificationTypeId'], 'members' => ['QualificationTypeId' => ['shape' => 'EntityId']]], 'DeleteQualificationTypeResponse' => ['type' => 'structure', 'members' => []], 'DeleteWorkerBlockRequest' => ['type' => 'structure', 'required' => ['WorkerId'], 'members' => ['WorkerId' => ['shape' => 'CustomerId'], 'Reason' => ['shape' => 'String']]], 'DeleteWorkerBlockResponse' => ['type' => 'structure', 'members' => []], 'DisassociateQualificationFromWorkerRequest' => ['type' => 'structure', 'required' => ['WorkerId', 'QualificationTypeId'], 'members' => ['WorkerId' => ['shape' => 'CustomerId'], 'QualificationTypeId' => ['shape' => 'EntityId'], 'Reason' => ['shape' => 'String']]], 'DisassociateQualificationFromWorkerResponse' => ['type' => 'structure', 'members' => []], 'EntityId' => ['type' => 'string', 'max' => 64, 'min' => 1, 'pattern' => '^[A-Z0-9]+$'], 'EventType' => ['type' => 'string', 'enum' => ['AssignmentAccepted', 'AssignmentAbandoned', 'AssignmentReturned', 'AssignmentSubmitted', 'AssignmentRejected', 'AssignmentApproved', 'HITCreated', 'HITExpired', 'HITReviewable', 'HITExtended', 'HITDisposed', 'Ping']], 'EventTypeList' => ['type' => 'list', 'member' => ['shape' => 'EventType']], 'ExceptionMessage' => ['type' => 'string'], 'GetAccountBalanceRequest' => ['type' => 'structure', 'members' => []], 'GetAccountBalanceResponse' => ['type' => 'structure', 'members' => ['AvailableBalance' => ['shape' => 'CurrencyAmount'], 'OnHoldBalance' => ['shape' => 'CurrencyAmount']]], 'GetAssignmentRequest' => ['type' => 'structure', 'required' => ['AssignmentId'], 'members' => ['AssignmentId' => ['shape' => 'EntityId']]], 'GetAssignmentResponse' => ['type' => 'structure', 'members' => ['Assignment' => ['shape' => 'Assignment'], 'HIT' => ['shape' => 'HIT']]], 'GetFileUploadURLRequest' => ['type' => 'structure', 'required' => ['AssignmentId', 'QuestionIdentifier'], 'members' => ['AssignmentId' => ['shape' => 'EntityId'], 'QuestionIdentifier' => ['shape' => 'String']]], 'GetFileUploadURLResponse' => ['type' => 'structure', 'members' => ['FileUploadURL' => ['shape' => 'String']]], 'GetHITRequest' => ['type' => 'structure', 'required' => ['HITId'], 'members' => ['HITId' => ['shape' => 'EntityId']]], 'GetHITResponse' => ['type' => 'structure', 'members' => ['HIT' => ['shape' => 'HIT']]], 'GetQualificationScoreRequest' => ['type' => 'structure', 'required' => ['QualificationTypeId', 'WorkerId'], 'members' => ['QualificationTypeId' => ['shape' => 'EntityId'], 'WorkerId' => ['shape' => 'CustomerId']]], 'GetQualificationScoreResponse' => ['type' => 'structure', 'members' => ['Qualification' => ['shape' => 'Qualification']]], 'GetQualificationTypeRequest' => ['type' => 'structure', 'required' => ['QualificationTypeId'], 'members' => ['QualificationTypeId' => ['shape' => 'EntityId']]], 'GetQualificationTypeResponse' => ['type' => 'structure', 'members' => ['QualificationType' => ['shape' => 'QualificationType']]], 'HIT' => ['type' => 'structure', 'members' => ['HITId' => ['shape' => 'EntityId'], 'HITTypeId' => ['shape' => 'EntityId'], 'HITGroupId' => ['shape' => 'EntityId'], 'HITLayoutId' => ['shape' => 'EntityId'], 'CreationTime' => ['shape' => 'Timestamp'], 'Title' => ['shape' => 'String'], 'Description' => ['shape' => 'String'], 'Question' => ['shape' => 'String'], 'Keywords' => ['shape' => 'String'], 'HITStatus' => ['shape' => 'HITStatus'], 'MaxAssignments' => ['shape' => 'Integer'], 'Reward' => ['shape' => 'CurrencyAmount'], 'AutoApprovalDelayInSeconds' => ['shape' => 'Long'], 'Expiration' => ['shape' => 'Timestamp'], 'AssignmentDurationInSeconds' => ['shape' => 'Long'], 'RequesterAnnotation' => ['shape' => 'String'], 'QualificationRequirements' => ['shape' => 'QualificationRequirementList'], 'HITReviewStatus' => ['shape' => 'HITReviewStatus'], 'NumberOfAssignmentsPending' => ['shape' => 'Integer'], 'NumberOfAssignmentsAvailable' => ['shape' => 'Integer'], 'NumberOfAssignmentsCompleted' => ['shape' => 'Integer']]], 'HITAccessActions' => ['type' => 'string', 'enum' => ['Accept', 'PreviewAndAccept', 'DiscoverPreviewAndAccept']], 'HITLayoutParameter' => ['type' => 'structure', 'required' => ['Name', 'Value'], 'members' => ['Name' => ['shape' => 'String'], 'Value' => ['shape' => 'String']]], 'HITLayoutParameterList' => ['type' => 'list', 'member' => ['shape' => 'HITLayoutParameter']], 'HITList' => ['type' => 'list', 'member' => ['shape' => 'HIT']], 'HITReviewStatus' => ['type' => 'string', 'enum' => ['NotReviewed', 'MarkedForReview', 'ReviewedAppropriate', 'ReviewedInappropriate']], 'HITStatus' => ['type' => 'string', 'enum' => ['Assignable', 'Unassignable', 'Reviewable', 'Reviewing', 'Disposed']], 'IdempotencyToken' => ['type' => 'string', 'max' => 64, 'min' => 1], 'Integer' => ['type' => 'integer'], 'IntegerList' => ['type' => 'list', 'member' => ['shape' => 'Integer']], 'ListAssignmentsForHITRequest' => ['type' => 'structure', 'required' => ['HITId'], 'members' => ['HITId' => ['shape' => 'EntityId'], 'NextToken' => ['shape' => 'PaginationToken'], 'MaxResults' => ['shape' => 'ResultSize'], 'AssignmentStatuses' => ['shape' => 'AssignmentStatusList']]], 'ListAssignmentsForHITResponse' => ['type' => 'structure', 'members' => ['NextToken' => ['shape' => 'PaginationToken'], 'NumResults' => ['shape' => 'Integer'], 'Assignments' => ['shape' => 'AssignmentList']]], 'ListBonusPaymentsRequest' => ['type' => 'structure', 'members' => ['HITId' => ['shape' => 'EntityId'], 'AssignmentId' => ['shape' => 'EntityId'], 'NextToken' => ['shape' => 'PaginationToken'], 'MaxResults' => ['shape' => 'ResultSize']]], 'ListBonusPaymentsResponse' => ['type' => 'structure', 'members' => ['NumResults' => ['shape' => 'Integer'], 'NextToken' => ['shape' => 'PaginationToken'], 'BonusPayments' => ['shape' => 'BonusPaymentList']]], 'ListHITsForQualificationTypeRequest' => ['type' => 'structure', 'required' => ['QualificationTypeId'], 'members' => ['QualificationTypeId' => ['shape' => 'EntityId'], 'NextToken' => ['shape' => 'PaginationToken'], 'MaxResults' => ['shape' => 'ResultSize']]], 'ListHITsForQualificationTypeResponse' => ['type' => 'structure', 'members' => ['NextToken' => ['shape' => 'PaginationToken'], 'NumResults' => ['shape' => 'Integer'], 'HITs' => ['shape' => 'HITList']]], 'ListHITsRequest' => ['type' => 'structure', 'members' => ['NextToken' => ['shape' => 'PaginationToken'], 'MaxResults' => ['shape' => 'ResultSize']]], 'ListHITsResponse' => ['type' => 'structure', 'members' => ['NextToken' => ['shape' => 'PaginationToken'], 'NumResults' => ['shape' => 'Integer'], 'HITs' => ['shape' => 'HITList']]], 'ListQualificationRequestsRequest' => ['type' => 'structure', 'members' => ['QualificationTypeId' => ['shape' => 'EntityId'], 'NextToken' => ['shape' => 'PaginationToken'], 'MaxResults' => ['shape' => 'ResultSize']]], 'ListQualificationRequestsResponse' => ['type' => 'structure', 'members' => ['NumResults' => ['shape' => 'Integer'], 'NextToken' => ['shape' => 'PaginationToken'], 'QualificationRequests' => ['shape' => 'QualificationRequestList']]], 'ListQualificationTypesRequest' => ['type' => 'structure', 'required' => ['MustBeRequestable'], 'members' => ['Query' => ['shape' => 'String'], 'MustBeRequestable' => ['shape' => 'Boolean'], 'MustBeOwnedByCaller' => ['shape' => 'Boolean'], 'NextToken' => ['shape' => 'PaginationToken'], 'MaxResults' => ['shape' => 'ResultSize']]], 'ListQualificationTypesResponse' => ['type' => 'structure', 'members' => ['NumResults' => ['shape' => 'Integer'], 'NextToken' => ['shape' => 'PaginationToken'], 'QualificationTypes' => ['shape' => 'QualificationTypeList']]], 'ListReviewPolicyResultsForHITRequest' => ['type' => 'structure', 'required' => ['HITId'], 'members' => ['HITId' => ['shape' => 'EntityId'], 'PolicyLevels' => ['shape' => 'ReviewPolicyLevelList'], 'RetrieveActions' => ['shape' => 'Boolean'], 'RetrieveResults' => ['shape' => 'Boolean'], 'NextToken' => ['shape' => 'PaginationToken'], 'MaxResults' => ['shape' => 'ResultSize']]], 'ListReviewPolicyResultsForHITResponse' => ['type' => 'structure', 'members' => ['HITId' => ['shape' => 'EntityId'], 'AssignmentReviewPolicy' => ['shape' => 'ReviewPolicy'], 'HITReviewPolicy' => ['shape' => 'ReviewPolicy'], 'AssignmentReviewReport' => ['shape' => 'ReviewReport'], 'HITReviewReport' => ['shape' => 'ReviewReport'], 'NextToken' => ['shape' => 'PaginationToken']]], 'ListReviewableHITsRequest' => ['type' => 'structure', 'members' => ['HITTypeId' => ['shape' => 'EntityId'], 'Status' => ['shape' => 'ReviewableHITStatus'], 'NextToken' => ['shape' => 'PaginationToken'], 'MaxResults' => ['shape' => 'ResultSize']]], 'ListReviewableHITsResponse' => ['type' => 'structure', 'members' => ['NextToken' => ['shape' => 'PaginationToken'], 'NumResults' => ['shape' => 'Integer'], 'HITs' => ['shape' => 'HITList']]], 'ListWorkerBlocksRequest' => ['type' => 'structure', 'members' => ['NextToken' => ['shape' => 'PaginationToken'], 'MaxResults' => ['shape' => 'ResultSize']]], 'ListWorkerBlocksResponse' => ['type' => 'structure', 'members' => ['NextToken' => ['shape' => 'PaginationToken'], 'NumResults' => ['shape' => 'Integer'], 'WorkerBlocks' => ['shape' => 'WorkerBlockList']]], 'ListWorkersWithQualificationTypeRequest' => ['type' => 'structure', 'required' => ['QualificationTypeId'], 'members' => ['QualificationTypeId' => ['shape' => 'EntityId'], 'Status' => ['shape' => 'QualificationStatus'], 'NextToken' => ['shape' => 'PaginationToken'], 'MaxResults' => ['shape' => 'ResultSize']]], 'ListWorkersWithQualificationTypeResponse' => ['type' => 'structure', 'members' => ['NextToken' => ['shape' => 'PaginationToken'], 'NumResults' => ['shape' => 'Integer'], 'Qualifications' => ['shape' => 'QualificationList']]], 'Locale' => ['type' => 'structure', 'required' => ['Country'], 'members' => ['Country' => ['shape' => 'CountryParameters'], 'Subdivision' => ['shape' => 'CountryParameters']]], 'LocaleList' => ['type' => 'list', 'member' => ['shape' => 'Locale']], 'Long' => ['type' => 'long'], 'NotificationSpecification' => ['type' => 'structure', 'required' => ['Destination', 'Transport', 'Version', 'EventTypes'], 'members' => ['Destination' => ['shape' => 'String'], 'Transport' => ['shape' => 'NotificationTransport'], 'Version' => ['shape' => 'String'], 'EventTypes' => ['shape' => 'EventTypeList']]], 'NotificationTransport' => ['type' => 'string', 'enum' => ['Email', 'SQS', 'SNS']], 'NotifyWorkersFailureCode' => ['type' => 'string', 'enum' => ['SoftFailure', 'HardFailure']], 'NotifyWorkersFailureStatus' => ['type' => 'structure', 'members' => ['NotifyWorkersFailureCode' => ['shape' => 'NotifyWorkersFailureCode'], 'NotifyWorkersFailureMessage' => ['shape' => 'String'], 'WorkerId' => ['shape' => 'CustomerId']]], 'NotifyWorkersFailureStatusList' => ['type' => 'list', 'member' => ['shape' => 'NotifyWorkersFailureStatus']], 'NotifyWorkersRequest' => ['type' => 'structure', 'required' => ['Subject', 'MessageText', 'WorkerIds'], 'members' => ['Subject' => ['shape' => 'String'], 'MessageText' => ['shape' => 'String'], 'WorkerIds' => ['shape' => 'CustomerIdList']]], 'NotifyWorkersResponse' => ['type' => 'structure', 'members' => ['NotifyWorkersFailureStatuses' => ['shape' => 'NotifyWorkersFailureStatusList']]], 'PaginationToken' => ['type' => 'string', 'max' => 255, 'min' => 1], 'ParameterMapEntry' => ['type' => 'structure', 'members' => ['Key' => ['shape' => 'String'], 'Values' => ['shape' => 'StringList']]], 'ParameterMapEntryList' => ['type' => 'list', 'member' => ['shape' => 'ParameterMapEntry']], 'PolicyParameter' => ['type' => 'structure', 'members' => ['Key' => ['shape' => 'String'], 'Values' => ['shape' => 'StringList'], 'MapEntries' => ['shape' => 'ParameterMapEntryList']]], 'PolicyParameterList' => ['type' => 'list', 'member' => ['shape' => 'PolicyParameter']], 'Qualification' => ['type' => 'structure', 'members' => ['QualificationTypeId' => ['shape' => 'EntityId'], 'WorkerId' => ['shape' => 'CustomerId'], 'GrantTime' => ['shape' => 'Timestamp'], 'IntegerValue' => ['shape' => 'Integer'], 'LocaleValue' => ['shape' => 'Locale'], 'Status' => ['shape' => 'QualificationStatus']]], 'QualificationList' => ['type' => 'list', 'member' => ['shape' => 'Qualification']], 'QualificationRequest' => ['type' => 'structure', 'members' => ['QualificationRequestId' => ['shape' => 'String'], 'QualificationTypeId' => ['shape' => 'EntityId'], 'WorkerId' => ['shape' => 'CustomerId'], 'Test' => ['shape' => 'String'], 'Answer' => ['shape' => 'String'], 'SubmitTime' => ['shape' => 'Timestamp']]], 'QualificationRequestList' => ['type' => 'list', 'member' => ['shape' => 'QualificationRequest']], 'QualificationRequirement' => ['type' => 'structure', 'required' => ['QualificationTypeId', 'Comparator'], 'members' => ['QualificationTypeId' => ['shape' => 'String'], 'Comparator' => ['shape' => 'Comparator'], 'IntegerValues' => ['shape' => 'IntegerList'], 'LocaleValues' => ['shape' => 'LocaleList'], 'RequiredToPreview' => ['shape' => 'Boolean', 'deprecated' => \true], 'ActionsGuarded' => ['shape' => 'HITAccessActions']]], 'QualificationRequirementList' => ['type' => 'list', 'member' => ['shape' => 'QualificationRequirement']], 'QualificationStatus' => ['type' => 'string', 'enum' => ['Granted', 'Revoked']], 'QualificationType' => ['type' => 'structure', 'members' => ['QualificationTypeId' => ['shape' => 'EntityId'], 'CreationTime' => ['shape' => 'Timestamp'], 'Name' => ['shape' => 'String'], 'Description' => ['shape' => 'String'], 'Keywords' => ['shape' => 'String'], 'QualificationTypeStatus' => ['shape' => 'QualificationTypeStatus'], 'Test' => ['shape' => 'String'], 'TestDurationInSeconds' => ['shape' => 'Long'], 'AnswerKey' => ['shape' => 'String'], 'RetryDelayInSeconds' => ['shape' => 'Long'], 'IsRequestable' => ['shape' => 'Boolean'], 'AutoGranted' => ['shape' => 'Boolean'], 'AutoGrantedValue' => ['shape' => 'Integer']]], 'QualificationTypeList' => ['type' => 'list', 'member' => ['shape' => 'QualificationType']], 'QualificationTypeStatus' => ['type' => 'string', 'enum' => ['Active', 'Inactive']], 'RejectAssignmentRequest' => ['type' => 'structure', 'required' => ['AssignmentId', 'RequesterFeedback'], 'members' => ['AssignmentId' => ['shape' => 'EntityId'], 'RequesterFeedback' => ['shape' => 'String']]], 'RejectAssignmentResponse' => ['type' => 'structure', 'members' => []], 'RejectQualificationRequestRequest' => ['type' => 'structure', 'required' => ['QualificationRequestId'], 'members' => ['QualificationRequestId' => ['shape' => 'String'], 'Reason' => ['shape' => 'String']]], 'RejectQualificationRequestResponse' => ['type' => 'structure', 'members' => []], 'RequestError' => ['type' => 'structure', 'members' => ['Message' => ['shape' => 'ExceptionMessage'], 'TurkErrorCode' => ['shape' => 'TurkErrorCode']], 'exception' => \true], 'ResultSize' => ['type' => 'integer', 'max' => 100, 'min' => 1], 'ReviewActionDetail' => ['type' => 'structure', 'members' => ['ActionId' => ['shape' => 'EntityId'], 'ActionName' => ['shape' => 'String'], 'TargetId' => ['shape' => 'EntityId'], 'TargetType' => ['shape' => 'String'], 'Status' => ['shape' => 'ReviewActionStatus'], 'CompleteTime' => ['shape' => 'Timestamp'], 'Result' => ['shape' => 'String'], 'ErrorCode' => ['shape' => 'String']]], 'ReviewActionDetailList' => ['type' => 'list', 'member' => ['shape' => 'ReviewActionDetail']], 'ReviewActionStatus' => ['type' => 'string', 'enum' => ['Intended', 'Succeeded', 'Failed', 'Cancelled']], 'ReviewPolicy' => ['type' => 'structure', 'required' => ['PolicyName'], 'members' => ['PolicyName' => ['shape' => 'String'], 'Parameters' => ['shape' => 'PolicyParameterList']]], 'ReviewPolicyLevel' => ['type' => 'string', 'enum' => ['Assignment', 'HIT']], 'ReviewPolicyLevelList' => ['type' => 'list', 'member' => ['shape' => 'ReviewPolicyLevel']], 'ReviewReport' => ['type' => 'structure', 'members' => ['ReviewResults' => ['shape' => 'ReviewResultDetailList'], 'ReviewActions' => ['shape' => 'ReviewActionDetailList']]], 'ReviewResultDetail' => ['type' => 'structure', 'members' => ['ActionId' => ['shape' => 'EntityId'], 'SubjectId' => ['shape' => 'EntityId'], 'SubjectType' => ['shape' => 'String'], 'QuestionId' => ['shape' => 'EntityId'], 'Key' => ['shape' => 'String'], 'Value' => ['shape' => 'String']]], 'ReviewResultDetailList' => ['type' => 'list', 'member' => ['shape' => 'ReviewResultDetail']], 'ReviewableHITStatus' => ['type' => 'string', 'enum' => ['Reviewable', 'Reviewing']], 'SendBonusRequest' => ['type' => 'structure', 'required' => ['WorkerId', 'BonusAmount', 'AssignmentId', 'Reason'], 'members' => ['WorkerId' => ['shape' => 'CustomerId'], 'BonusAmount' => ['shape' => 'CurrencyAmount'], 'AssignmentId' => ['shape' => 'EntityId'], 'Reason' => ['shape' => 'String'], 'UniqueRequestToken' => ['shape' => 'IdempotencyToken']]], 'SendBonusResponse' => ['type' => 'structure', 'members' => []], 'SendTestEventNotificationRequest' => ['type' => 'structure', 'required' => ['Notification', 'TestEventType'], 'members' => ['Notification' => ['shape' => 'NotificationSpecification'], 'TestEventType' => ['shape' => 'EventType']]], 'SendTestEventNotificationResponse' => ['type' => 'structure', 'members' => []], 'ServiceFault' => ['type' => 'structure', 'members' => ['Message' => ['shape' => 'ExceptionMessage'], 'TurkErrorCode' => ['shape' => 'TurkErrorCode']], 'exception' => \true, 'fault' => \true], 'String' => ['type' => 'string'], 'StringList' => ['type' => 'list', 'member' => ['shape' => 'String']], 'Timestamp' => ['type' => 'timestamp'], 'TurkErrorCode' => ['type' => 'string'], 'UpdateExpirationForHITRequest' => ['type' => 'structure', 'required' => ['HITId', 'ExpireAt'], 'members' => ['HITId' => ['shape' => 'EntityId'], 'ExpireAt' => ['shape' => 'Timestamp']]], 'UpdateExpirationForHITResponse' => ['type' => 'structure', 'members' => []], 'UpdateHITReviewStatusRequest' => ['type' => 'structure', 'required' => ['HITId'], 'members' => ['HITId' => ['shape' => 'EntityId'], 'Revert' => ['shape' => 'Boolean']]], 'UpdateHITReviewStatusResponse' => ['type' => 'structure', 'members' => []], 'UpdateHITTypeOfHITRequest' => ['type' => 'structure', 'required' => ['HITId', 'HITTypeId'], 'members' => ['HITId' => ['shape' => 'EntityId'], 'HITTypeId' => ['shape' => 'EntityId']]], 'UpdateHITTypeOfHITResponse' => ['type' => 'structure', 'members' => []], 'UpdateNotificationSettingsRequest' => ['type' => 'structure', 'required' => ['HITTypeId'], 'members' => ['HITTypeId' => ['shape' => 'EntityId'], 'Notification' => ['shape' => 'NotificationSpecification'], 'Active' => ['shape' => 'Boolean']]], 'UpdateNotificationSettingsResponse' => ['type' => 'structure', 'members' => []], 'UpdateQualificationTypeRequest' => ['type' => 'structure', 'required' => ['QualificationTypeId'], 'members' => ['QualificationTypeId' => ['shape' => 'EntityId'], 'Description' => ['shape' => 'String'], 'QualificationTypeStatus' => ['shape' => 'QualificationTypeStatus'], 'Test' => ['shape' => 'String'], 'AnswerKey' => ['shape' => 'String'], 'TestDurationInSeconds' => ['shape' => 'Long'], 'RetryDelayInSeconds' => ['shape' => 'Long'], 'AutoGranted' => ['shape' => 'Boolean'], 'AutoGrantedValue' => ['shape' => 'Integer']]], 'UpdateQualificationTypeResponse' => ['type' => 'structure', 'members' => ['QualificationType' => ['shape' => 'QualificationType']]], 'WorkerBlock' => ['type' => 'structure', 'members' => ['WorkerId' => ['shape' => 'CustomerId'], 'Reason' => ['shape' => 'String']]], 'WorkerBlockList' => ['type' => 'list', 'member' => ['shape' => 'WorkerBlock']]]];

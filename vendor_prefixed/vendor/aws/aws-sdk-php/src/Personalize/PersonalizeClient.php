<?php

namespace WPCOM_VIP\Aws\Personalize;

use WPCOM_VIP\Aws\AwsClient;
/**
 * This client is used to interact with the **Amazon Personalize** service.
 * @method \Aws\Result createBatchInferenceJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createBatchInferenceJobAsync(array $args = [])
 * @method \Aws\Result createCampaign(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createCampaignAsync(array $args = [])
 * @method \Aws\Result createDataset(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDatasetAsync(array $args = [])
 * @method \Aws\Result createDatasetGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDatasetGroupAsync(array $args = [])
 * @method \Aws\Result createDatasetImportJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createDatasetImportJobAsync(array $args = [])
 * @method \Aws\Result createEventTracker(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createEventTrackerAsync(array $args = [])
 * @method \Aws\Result createFilter(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createFilterAsync(array $args = [])
 * @method \Aws\Result createSchema(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSchemaAsync(array $args = [])
 * @method \Aws\Result createSolution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSolutionAsync(array $args = [])
 * @method \Aws\Result createSolutionVersion(array $args = [])
 * @method \GuzzleHttp\Promise\Promise createSolutionVersionAsync(array $args = [])
 * @method \Aws\Result deleteCampaign(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteCampaignAsync(array $args = [])
 * @method \Aws\Result deleteDataset(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteDatasetAsync(array $args = [])
 * @method \Aws\Result deleteDatasetGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteDatasetGroupAsync(array $args = [])
 * @method \Aws\Result deleteEventTracker(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteEventTrackerAsync(array $args = [])
 * @method \Aws\Result deleteFilter(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteFilterAsync(array $args = [])
 * @method \Aws\Result deleteSchema(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteSchemaAsync(array $args = [])
 * @method \Aws\Result deleteSolution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise deleteSolutionAsync(array $args = [])
 * @method \Aws\Result describeAlgorithm(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeAlgorithmAsync(array $args = [])
 * @method \Aws\Result describeBatchInferenceJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeBatchInferenceJobAsync(array $args = [])
 * @method \Aws\Result describeCampaign(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeCampaignAsync(array $args = [])
 * @method \Aws\Result describeDataset(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeDatasetAsync(array $args = [])
 * @method \Aws\Result describeDatasetGroup(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeDatasetGroupAsync(array $args = [])
 * @method \Aws\Result describeDatasetImportJob(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeDatasetImportJobAsync(array $args = [])
 * @method \Aws\Result describeEventTracker(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeEventTrackerAsync(array $args = [])
 * @method \Aws\Result describeFeatureTransformation(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeFeatureTransformationAsync(array $args = [])
 * @method \Aws\Result describeFilter(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeFilterAsync(array $args = [])
 * @method \Aws\Result describeRecipe(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeRecipeAsync(array $args = [])
 * @method \Aws\Result describeSchema(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSchemaAsync(array $args = [])
 * @method \Aws\Result describeSolution(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSolutionAsync(array $args = [])
 * @method \Aws\Result describeSolutionVersion(array $args = [])
 * @method \GuzzleHttp\Promise\Promise describeSolutionVersionAsync(array $args = [])
 * @method \Aws\Result getSolutionMetrics(array $args = [])
 * @method \GuzzleHttp\Promise\Promise getSolutionMetricsAsync(array $args = [])
 * @method \Aws\Result listBatchInferenceJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listBatchInferenceJobsAsync(array $args = [])
 * @method \Aws\Result listCampaigns(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listCampaignsAsync(array $args = [])
 * @method \Aws\Result listDatasetGroups(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDatasetGroupsAsync(array $args = [])
 * @method \Aws\Result listDatasetImportJobs(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDatasetImportJobsAsync(array $args = [])
 * @method \Aws\Result listDatasets(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listDatasetsAsync(array $args = [])
 * @method \Aws\Result listEventTrackers(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listEventTrackersAsync(array $args = [])
 * @method \Aws\Result listFilters(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listFiltersAsync(array $args = [])
 * @method \Aws\Result listRecipes(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listRecipesAsync(array $args = [])
 * @method \Aws\Result listSchemas(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSchemasAsync(array $args = [])
 * @method \Aws\Result listSolutionVersions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSolutionVersionsAsync(array $args = [])
 * @method \Aws\Result listSolutions(array $args = [])
 * @method \GuzzleHttp\Promise\Promise listSolutionsAsync(array $args = [])
 * @method \Aws\Result updateCampaign(array $args = [])
 * @method \GuzzleHttp\Promise\Promise updateCampaignAsync(array $args = [])
 */
class PersonalizeClient extends \WPCOM_VIP\Aws\AwsClient
{
}

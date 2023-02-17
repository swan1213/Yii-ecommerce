<?php

namespace Smartling\Jobs;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Jobs\Params\AddFileToJobParameters;
use Smartling\Jobs\Params\AddLocaleToJobParameters;
use Smartling\Jobs\Params\CancelJobParameters;
use Smartling\Jobs\Params\CreateJobParameters;
use Smartling\Jobs\Params\ListJobsParameters;
use Smartling\Jobs\Params\SearchJobsParameters;
use Smartling\Jobs\Params\UpdateJobParameters;

/**
 * Class JobsApi
 *
 * @package Smartling\Project
 */
class JobsApi extends BaseApiAbstract
{

    const ENDPOINT_URL = 'https://api.smartling.com/jobs-api/v3/projects';

    /**
     * Timeout forsync requests in seconds.
     *
     * @var int
     */
    private $syncTimeOut = 15;

    /**
     * @return int
     */
    public function getSyncTimeOut() {
        return $this->syncTimeOut;
    }

    /**
     * @param int $syncTimeOut
     */
    public function setSyncTimeOut($syncTimeOut) {
        $this->syncTimeOut = $syncTimeOut;
    }

    /**
     * Makes async operation sync.
     *
     * @param mixed $response
     * @throws SmartlingApiException
     */
    private function wait($response) {
        if (is_array($response) && !empty($response['url'])) {
            $explodedUrl = explode('/', $response['url']);
            $arrayLength = count($explodedUrl);
            $processId = $explodedUrl[$arrayLength - 1];
            $jobId = $explodedUrl[$arrayLength - 3];
            $start_time = time();

            do {
                $delta = time() - $start_time;

                if ($delta > $this->getSyncTimeOut()) {
                    throw new SmartlingApiException(vsprintf('No response received after %s seconds.', [$delta]));
                }

                sleep(1);
                $result = $this->checkAsynchronousProcessingStatus($jobId, $processId);
            }
            while ($result['processState'] != 'COMPLETED');
        }
    }

    /**
     * Instantiates Jobs API object.
     *
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return JobsApi
     */
    public static function create(AuthApiInterface $authProvider, $projectId, $logger = null)
    {

        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($projectId, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    /**
     * Creates a job.
     *
     * @param CreateJobParameters $parameters
     * @return array
     * @throws SmartlingApiException
     */
    public function createJob(CreateJobParameters $parameters)
    {
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());
        $request = $this->prepareHttpRequest('jobs', $requestData, self::HTTP_METHOD_POST);

        return $this->sendRequest($request);
    }

    /**
     * Updates a job.
     *
     * @param string $jobId
     * @param UpdateJobParameters $parameters
     * @return array
     * @throws SmartlingApiException
     */
    public function updateJob($jobId, UpdateJobParameters $parameters)
    {
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());
        $request = $this->prepareHttpRequest('jobs/' . $jobId, $requestData, self::HTTP_METHOD_PUT);

        return $this->sendRequest($request);
    }

    /**
     * Cancels a job synchronously.
     *
     * @param string $jobId
     * @param CancelJobParameters $parameters
     * @throws SmartlingApiException
     */
    public function cancelJobSync($jobId, CancelJobParameters $parameters)
    {
        $endpoint = vsprintf('jobs/%s/cancel', [$jobId]);
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());
        $request = $this->prepareHttpRequest($endpoint, $requestData, self::HTTP_METHOD_POST);

        $this->wait($this->sendRequest($request));
    }

    /**
     * Returns a list of jobs.
     *
     * @param ListJobsParameters $parameters
     * @return array
     * @throws SmartlingApiException
     */
    public function listJobs(ListJobsParameters $parameters)
    {
        $requestData = $this->getDefaultRequestData('query', $parameters->exportToArray());
        $request = $this->prepareHttpRequest('jobs', $requestData, self::HTTP_METHOD_GET);

        return $this->sendRequest($request);
    }

    /**
     * Returns a job.
     *
     * @param string $jobId
     * @return array
     * @throws SmartlingApiException
     */
    public function getJob($jobId)
    {
        $requestData = $this->getDefaultRequestData('query', []);
        $request = $this->prepareHttpRequest('jobs/' . $jobId, $requestData, self::HTTP_METHOD_GET);

        return $this->sendRequest($request);
    }

    /**
     * Authorizes a job.
     *
     * @param $jobId
     * @throws SmartlingApiException
     */
    public function authorizeJob($jobId)
    {
        $endpoint = vsprintf('jobs/%s/authorize', [$jobId]);
        $requestData = $this->getDefaultRequestData('json', new \stdClass());
        $requestData['headers']['Content-Type'] = 'application/json';
        $request = $this->prepareHttpRequest($endpoint, $requestData, self::HTTP_METHOD_POST);

        $this->sendRequest($request);
    }

    /**
     * Adds file to a job synchronously.
     *
     * @param $jobId
     * @param AddFileToJobParameters $parameters
     * @throws SmartlingApiException
     */
    public function addFileToJobSync($jobId, AddFileToJobParameters $parameters)
    {
        $endpoint = vsprintf('jobs/%s/file/add', [$jobId]);
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());
        $request = $this->prepareHttpRequest($endpoint, $requestData, self::HTTP_METHOD_POST);

        $this->wait($this->sendRequest($request));
    }

    /**
     * Search/Find Job(s), based on different query criteria passed in.
     *
     * @param SearchJobsParameters $parameters
     *
     * @return array
     * @throws SmartlingApiException
     */
    public function searchJobs(SearchJobsParameters $parameters)
    {
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());
        $request = $this->prepareHttpRequest('jobs/search', $requestData, self::HTTP_METHOD_POST);

        return $this->sendRequest($request);
    }

    /**
     * Adds locale to a job synchronously.
     *
     * @param string $jobId
     * @param string $localeId
     * @param AddLocaleToJobParameters $parameters
     * @throws SmartlingApiException
     */
    public function addLocaleToJobSync($jobId, $localeId, AddLocaleToJobParameters $parameters)
    {
        $endpoint = vsprintf('jobs/%s/locales/%s', [$jobId, $localeId]);
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());
        $request = $this->prepareHttpRequest($endpoint, $requestData, self::HTTP_METHOD_POST);
        $this->wait($this->sendRequest($request));
    }

    /**
     * Checks status of async process.
     *
     * @param string $jobId
     * @param string $processId
     * @return array
     * @throws SmartlingApiException
     */
    public function checkAsynchronousProcessingStatus($jobId, $processId)
    {
        $endpoint = vsprintf('jobs/%s/processes/%s', [$jobId, $processId]);
        $requestData = $this->getDefaultRequestData('query', []);
        $request = $this->prepareHttpRequest($endpoint, $requestData, self::HTTP_METHOD_GET);

        return $this->sendRequest($request);
    }

}

<?php

/**
 * This file contains example job flow.
 *
 * How to use:
 * run "php example.php --project-id={PROJECT_ID} --user-id={USER_IDENTIFIER} --secret-key={SECRET_KEY}" in console
 *
 * Be sure you that dependencies are solved by composer BEFORE running.
 */

use Smartling\Exceptions\SmartlingApiException;
use Smartling\File\FileApi;
use Smartling\Jobs\JobsApi;
use Smartling\Jobs\Params\AddFileToJobParameters;
use Smartling\Jobs\Params\AddLocaleToJobParameters;
use Smartling\Jobs\Params\CancelJobParameters;

/* $longOpts = [
    'project-id:',
    'user-id:',
    'secret-key:',
];

$options = getopt('', $longOpts); */
$options = [
  'project-id' => '208e616a4',
  'user-id' => 'vclpkdnhrwqcghxdufdlvzppetmqxh',
  'secret-key' => '67bgpj9279ed5oa62897tk27jkBw)lfb60bcqhs7b7tbtt39c5nqaub',
];

if (
    !array_key_exists('project-id', $options)
    || !array_key_exists('user-id', $options)
    || !array_key_exists('secret-key', $options)
) {
    echo 'Missing required params.' . PHP_EOL;
    exit;
}

$autoloader = 'vendor/autoload.php';

if (!file_exists($autoloader) || !is_readable($autoloader)) {
    echo 'Error. Autoloader not found. Seems you didn\'t run:' . PHP_EOL . '    composer update' . PHP_EOL;
    exit;
} else {
    /** @noinspection UntrustedInclusionInspection */
    require_once 'vendor/autoload.php';
}

$projectId = $options['project-id'];
$userIdentifier = $options['user-id'];
$userSecretKey = $options['secret-key'];
$authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

/**
 * Recommended flow.
 *
 * 0. Upload needed file with a help of FileAPI.
 * 1. Create a job without locales.
 * 2. Add needed locales to a job.
 * 3. Attach file to a job.
 * 4. Authorize a job.
 * 5. Cancel job when it's needed.
 *
 * For more demos see jobs-sdk-php/example.php.
 */
$jobsAPI = JobsApi::create($authProvider, $projectId);
$fileAPI = FileApi::create($authProvider, $projectId);

try {
    $localeId = 'zh-CN';
    $fileUri = 'Data.json';
    $fileName = 'Data.json';

    // Upload file.
    $fileAPI->uploadFile($fileUri, $fileName, 'json');

    // Create a job without locales.
    $createParams = new \Smartling\Jobs\Params\CreateJobParameters();
    $createParams->setName("T1111111111est Job Name " . time());
    $createParams->setDescription("T1111111111est Job Description " . time());
    $createParams->setDueDate(DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-01 19:19:17', new DateTimeZone('UTC')));
	
    $job = $jobsAPI->createJob($createParams);
    echo '<pre>'; print_r($job); echo '</pre>';

    // Add locale to a job.
    $addLocaleParams = new AddLocaleToJobParameters();
    $addLocaleParams->setSyncContent(false);
    $jobsAPI->addLocaleToJobSync($job['translationJobUid'], $localeId, $addLocaleParams);

    // Attach uploaded file to a job.
    $addFileParams = new AddFileToJobParameters();
    $addFileParams->setTargetLocales([$localeId]);
    $addFileParams->setFileUri($fileName);
	// echo '<pre>'; print_r($addFileParams); echo '</pre>';
    $jobsAPI->addFileToJobSync($job['translationJobUid'], $addFileParams);

    // Authorize job.
    $jobsAPI->authorizeJob($job['translationJobUid']);

    // Cancel job.
   /*  $cancelParams = new CancelJobParameters();
    $cancelParams->setReason('Some reason to cancel');
    $jobsAPI->cancelJobSync($job['translationJobUid'], $cancelParams); */
}
catch (SmartlingApiException $e) {
    echo '<pre>'; print_r($e);
}

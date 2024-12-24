<?php

namespace App\Helper;

use Google\Cloud\Tasks\V2\CloudTasksClient;
use Google\Cloud\Tasks\V2\HttpMethod;
use Google\Cloud\Tasks\V2\HttpRequest;
use Google\Cloud\Tasks\V2\Task;


class GoogleCloudTask
{

    public static function createTask($url, $data)
    {
        $client = new CloudTasksClient();
        $config = config('app');
        $project = $config['CLOUD_PROJECT'];
        $location = $config['CLOUD_LOCATION'];
        $queue = $config['CLOUD_QUEUE'];
        // $queueName = $client::queueName($project, $location, $queue);
        if (!empty($project)) {
            $cloudTasksClient = new CloudTasksClient();
            try {
                $formattedParent = $cloudTasksClient->queueName($project, $location, $queue);
                $task = new Task();
                $httpRequest = new HttpRequest();
                // The full url path that the task request will be sent to.
                $httpRequest->setUrl($url);
                // POST is the default HTTP method, but any HTTP method can be used.
                $httpRequest->setHttpMethod(HttpMethod::POST);
                if (isset($data)) {
                    $httpRequest->setBody($data);
                }
                $task->setHttpRequest($httpRequest);
                $response = $cloudTasksClient->createTask($formattedParent, $task);
                //printf('Created task %s' . PHP_EOL, $response->getName());
            } finally {
                $cloudTasksClient->close();
            }
        }
    }
}

<?php

use TYPO3\CMS\Backend\Controller;

return [
    'jobman_all_jobs' => [
        'path' => '/jobman/jobs/all',
        'target' => \Lanius\Jobman\Controller\Backend\JobsController::class . '::allJobsAction',
    ],
];

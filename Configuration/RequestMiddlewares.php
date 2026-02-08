<?php

return [
    'frontend' => [
        'jobman/job-view-counter' => [
            'target' => \Lanius\Jobman\Middleware\JobViewCounterMiddleware::class,
            'after' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];

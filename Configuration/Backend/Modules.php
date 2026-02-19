<?php

declare(strict_types=1);

use Lanius\Jobman\Controller\DashboardModuleController;
use Lanius\Jobman\Controller\Backend\JobsController;

return [

    // 🔹 Top-Level Kategorie (KEIN parent!)
    'jobman' => [
        'position' => ['after' => 'content'],
        'iconIdentifier' => 'jobman-plugin-joblist',
        'labels' => [
            'title' => 'Job Manager',
        ],
    ],

    'jobman_dashboard' => [
        'parent' => 'jobman',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/jobman/dashboard',
        'labels' => [
            'title' => 'Dashboard',
        ],
        'extensionName' => 'Jobman',
        'iconIdentifier' => 'jobman-plugin-joblist',
        'controllerActions' => [
            DashboardModuleController::class => [
                'index',
            ],
        ],
    ],

    'jobman_jobs' => [
        'parent' => 'jobman',
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/jobman/jobs',
        'labels' => [
            'title' => 'Jobs',
        ],
        'extensionName' => 'Jobman',
        'iconIdentifier' => 'jobman-plugin-joblist',
        'controllerActions' => [
            JobsController::class => [
                'index',
                'allJobs'
            ],
        ],
        'navigationComponent' => '@typo3/backend/tree/page-tree-element',
    ],

];

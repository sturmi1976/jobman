<?php

declare(strict_types=1);

use Lanius\Jobman\Controller\DashboardModuleController;

return [

    // 🔹 Top-Level Kategorie (KEIN parent!)
    'jobman' => [
        'position' => ['after' => 'content'],
        'iconIdentifier' => 'jobman-plugin-joblist',
        'labels' => [
            'title' => 'Job Manager',
        ],
    ],

    // 🔹 Dashboard Modul darunter
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
        'js' => 'EXT:jobman/Resources/Public/JavaScript/dashboard.js',
        'controllerActions' => [
            DashboardModuleController::class => [
                'index',
            ],
        ],
    ],
];

<?php

return [
    'dependencies' => [
        'backend',
        'core',
    ],
    'imports' => [
        // recursive definiton, all *.js files in this folder are import-mapped
        // trailing slash is required per importmap-specification
        '@lanius/jobman/' => 'EXT:jobman/Resources/Public/JavaScript/',
    ],
];

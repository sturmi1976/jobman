<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Job Manager',
    'description' => 'Job listings with Google for Jobs microdata and tt_address support, featuring flexible display options such as accordions, tiles, and detailed job views.',
    'category' => 'plugin',
    'author' => 'Andre Lanius',
    'author_email' => 'a-lanius@web.de',
    'state' => 'stable',
    'version' => '1.5.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.9.99',
            'tt_address' => '0.0.0-0.0.0',
            'php' => '8.2.0-8.4.99',
        ],
    ],
];

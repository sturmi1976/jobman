<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Job Manager',
    'description' => 'Manage job postings in TYPO3',
    'category' => 'plugin',
    'author' => 'Andre Lanius',
    'author_email' => 'a-lanius@web.de',
    'state' => 'beta',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.9.99',
            'tt_address' => '0.0.0-0.0.0',
        ],
    ],
];

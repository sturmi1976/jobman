<?php

return [
    'ctrl' => [
        'title' => 'Applications / Bewerbungen',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'searchFields' => 'name,email,message',
        'iconfile' => 'EXT:jobman/Resources/Public/Icons/job.svg',
    ],
    'types' => [
        '1' => [
            'showitem' => '
                hidden,
                job, name, email, message,
                --div--;Files, files
            '
        ],
    ],
    'columns' => [

        'hidden' => [
            'config' => ['type' => 'check']
        ],

        'job' => [
            'label' => 'Job',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_jobman_domain_model_job',
                'default' => 0,
            ],
        ],

        'name' => [
            'label' => 'Name',
            'config' => [
                'type' => 'input',
                'size' => 30,
            ],
        ],

        'email' => [
            'label' => 'E-Mail',
            'config' => [
                'type' => 'input',
                'eval' => 'email',
            ],
        ],

        'message' => [
            'label' => 'Message',
            'config' => [
                'type' => 'text',
            ],
        ],

        'files' => [
            'label' => 'Attachments',
            'config' => [
                'type' => 'file',
                'appearance' => [
                    'createNewRelationLinkTitle' => 'Add file',
                ],
                'maxitems' => 10,
                'allowed' => 'pdf,doc,docx,jpg,jpeg,png,zip',
            ],
        ],
    ],
];

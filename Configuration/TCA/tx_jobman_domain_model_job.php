<?php

return [
    'ctrl' => [
        'title' => 'Job',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'title,description,location',
        'iconfile' => 'EXT:jobman/Resources/Public/Icons/job.svg',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
    ],

    'columns' => [
        'title' => [
            'label' => 'LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.title',
            'config' => ['type' => 'input', 'eval' => 'trim,required'],
        ],
        'description' => [
            'label' => 'LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
            ],
        ],
        'slug' => [
            'label' => 'URL-Slug',
            'config' => [
                'type' => 'slug',
                'generatorOptions' => [
                    'fields' => ['title'],  // Felder, aus denen der Slug erstellt wird
                    'replacements' => [
                        '/' => '-',           // optional: Ersatz von Zeichen
                    ],
                ],
                'fallbackCharacter' => '-', // Zeichen für ungültige Zeichen
                'eval' => 'uniqueInSite',   // stellt eindeutigen Slug pro Site sicher
            ],
        ],
        'location' => ['label' => 'LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.location', 'config' => ['type' => 'input']],
        'employment_type' => [
            'label' => 'LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.employment_type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.employment_type.fulltime', 'FULL_TIME'],
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.employment_type.parttime', 'PART_TIME'],
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.employment_type.freelance', 'CONTRACTOR'],
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.employment_type.temporary', 'TEMPORARY'],
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.employment_type.intern', 'INTERN'],
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.employment_type.volunteer', 'VOLUNTEER'],
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.employment_type.per_diem', 'PER_DIEM'],
                ],
            ],
        ],

        'salary' => [
            'label' => 'LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.salary',
            'config' => ['type' => 'input'],
        ],

        'remote' => [
            'label' => 'LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.remote',
            'config' => [
                'type' => 'check',
                'default' => 0,
                'onChange' => 'reload',
            ],
        ],

        'remote_type' => [
            'label' => 'LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.remote_type',
            'displayCond' => 'FIELD:remote:REQ:true',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.remote_type.hybrid', 'HYBRID'],
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.remote_type.full_remote', 'FULL_REMOTE'],
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.remote_type.remote_eu', 'REMOTE_EU'],
                    ['LLL:EXT:jobman/Resources/Private/Language/locallang_db.xlf:tx_jobman_domain_model_job.remote_type.remote_world', 'REMOTE_WORLD'],
                ],
                'default' => '',
            ],
        ],



        'show_button' => [
            'label' => 'Bewerbungs-Button anzeigen',
            'config' => [
                'type' => 'check',
                'default' => 0,
                'onChange' => 'reload',
            ],
        ],

        'button_type' => [
            'label' => 'Button Typ',
            'displayCond' => 'FIELD:show_button:REQ:true',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Bewerbungsformular', 'form'],
                    ['Externer Link', 'link'],
                ],
                'default' => '',
                'onChange' => 'reload',
            ],
        ],

        'extern_link' => [
            'label' => 'Externer Link zum Bewerbungsformular',
            'description' => 'Hier kannst du einen externen Link zu einem Bewerbungsformular oder einem Stellenagebot angeben.',
            'displayCond' => 'FIELD:button_type:=:link',
            'config' => [
                'type' => 'input',
                //'renderType' => 'inputLink',
                'eval' => 'trim',
            ],
        ],

        'email' => [
            'label' => 'E-Mail Adresse wohin die Bewerbung gesendet wird',
            'description' => 'Alle Daten des Bewerbers inkl. Anhänge werden an diese E-Mail versendet.',
            'displayCond' => 'FIELD:button_type:=:form',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
            ],
        ],


        'address_mode' => [
            'label' => 'Kontaktadresse',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['---', ''],
                    ['Adresse aus tt_address auswählen', 'tt_address'],
                    ['Adresse manuell eingeben', 'manual'],
                ],
                'default' => '',
                'onChange' => 'reload',
            ],
        ],

        'address_tt' => [
            'label' => 'Adresse auswählen',
            'displayCond' => 'FIELD:address_mode:=:tt_address',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tt_address',
                'foreign_table_where' => 'AND tt_address.deleted = 0 AND tt_address.hidden = 0 ORDER BY tt_address.name',
                'items' => [
                    ['---', 0],
                ],
                'default' => 0,
            ],
        ],


        'address_manual' => [
            'label' => 'Adresse (manuell)',
            'displayCond' => 'FIELD:address_mode:=:manual',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
                'cols' => 40,
                'rows' => 6,
            ],
        ],


        'valid_through' => [
            'label' => 'Bewerbungsfrist (für Google for Jobs) - Optional',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
                'default' => 0
            ],
        ],


        // Structured Data fields
        'sd_company' => [
            'label' => 'Firma',
            'config' => ['type' => 'input', 'size' => 50],
        ],
        'sd_street' => [
            'label' => 'Straße',
            'config' => ['type' => 'input', 'size' => 50],
        ],
        'sd_postalcode' => [
            'label' => 'Postleitzahl',
            'config' => ['type' => 'input', 'size' => 20],
        ],
        'sd_city' => [
            'label' => 'Stadt',
            'config' => ['type' => 'input', 'size' => 30],
        ],
        'sd_region' => [
            'label' => 'Bundesland / Region',
            'config' => [
                'type' => 'input',
                'size' => 20,
            ],
        ],
        'sd_country' => [
            'label' => 'Land',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Deutschland', 'DE'],
                    ['Österreich', 'AT'],
                    ['Schweiz', 'CH'],
                ],
                'default' => 'DE',
            ],
        ],




        // System fields
        'hidden' => ['label' => 'Versteckt', 'config' => ['type' => 'check']],
        'starttime' => ['label' => 'Startzeit', 'config' => ['type' => 'datetime']],
        'endtime' => ['label' => 'Endzeit', 'config' => ['type' => 'datetime']],
        'sys_language_uid' => ['label' => 'Sprache', 'config' => ['type' => 'language']],
        'l10n_parent' => [
            'label' => 'Übersetzung von',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [['', 0]],
                'foreign_table' => 'tx_jobman_domain_model_job',
                'foreign_table_where' => 'AND tx_jobman_domain_model_job.pid=###CURRENT_PID### AND tx_jobman_domain_model_job.sys_language_uid IN (-1,0)',
            ],
        ],

        /*
        'settings' => [
            'label' => 'Zusatzinfos (FlexForm)',
            'config' => [
                'type' => 'flex',
                'ds' => 'FILE:EXT:jobman/Configuration/FlexForms/JobSettings.xml',
            ],
        ],
        */
    ],

    'types' => [
        '0' => [
            'showitem' => '
--div--;core.form.tabs:general,
title, slug, location, employment_type, remote, remote_type, show_button, button_type, extern_link, email, settings,
--div--;Details,
description, salary,
--div--;Kontakt,
address_mode, address_tt, address_manual,
--div--;Structured Data,
sd_company, sd_street, sd_postalcode, sd_city, sd_region, sd_country, valid_through,
--div--;core.form.tabs:language,--palette--;;paletteLanguage,
--div--;core.form.tabs:access,hidden,--palette--;;paletteTimes,
',
        ],
    ],


    'palettes' => [
        'visibility' => ['showitem' => 'hidden, starttime, endtime', 'canNotCollapse' => 1],
        //'language' => ['showitem' => 'sys_language_uid, l10n_parent', 'canNotCollapse' => 1],
        'paletteLanguage' => [
            'showitem' => 'sys_language_uid, l10n_parent',
        ],
        'paletteTimes' => [
            'showitem' => 'starttime,endtime',
        ],
    ],
];

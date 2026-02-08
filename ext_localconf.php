<?php
defined('TYPO3') or die();

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Lanius\Jobman\Controller\JobController;

ExtensionUtility::configurePlugin(
    'Jobman',
    'Pi1',
    [
        JobController::class => 'list, show'
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

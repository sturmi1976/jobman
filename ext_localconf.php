<?php
defined('TYPO3') or die();


use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use Lanius\Jobman\Controller\JobController;

ExtensionUtility::configurePlugin(
    'Jobman',
    'Pi1',
    [
        JobController::class => 'list, show, application, submitApplication, success'
    ],
    [
        JobController::class => 'application, submitApplication, success'
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT,
);

// Email Template and Layout path
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][700] = 'EXT:jobman/Resources/Private/Templates/Email';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths'][700] = 'EXT:jobman/Resources/Private/Layouts/Email';

//CSS for backend
$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['jobman_general'] = 'EXT:jobman/Resources/Public/Css/backend.css';
$GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['jobman_dashboard'] = 'EXT:jobman/Resources/Public/Css/dashboard.css';

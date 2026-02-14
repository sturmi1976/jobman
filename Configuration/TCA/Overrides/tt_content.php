<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;




// Eigene Kategorie „Forum“ registrieren
ExtensionManagementUtility::addTcaSelectItem(
    'tt_content',
    'CType',
    [
        'Jobman',

        // Label
        '--div--',

        // Separator im Dropdown
        'content-special',
    ],
    '--div--',

    // nach welchem Element einfügen (hier am Ende)
    'after'
);

// Plugin registrieren
$pluginKey = ExtensionUtility::registerPlugin(
    'Jobman',          // Extension-Key
    'Pi1',             // Plugin-Key (muss exakt so wie bei configurePlugin)
    'Job List',        // Plugin-Name im BE
    'jobman-plugin-joblist', // Icon (optional)
    'Jobman',                // Plugin type (leer lassen)
    'Beschreibung für die Jobextension.',                // Plugin description (optional)
    'FILE:EXT:jobman/Configuration/FlexForms/ListViewSettings.xml',
);



ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;Configuration,pi_flexform,',
    $pluginKey,
    'after:subheader',
);

ExtensionManagementUtility::addPiFlexFormValue(
    '',
    'FILE:EXT:jobman/Configuration/FlexForms/ListViewSettings.xml',
    $pluginKey,
);

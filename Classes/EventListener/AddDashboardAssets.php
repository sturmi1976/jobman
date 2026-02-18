<?php

namespace Lanius\Jobman\EventListener;

use TYPO3\CMS\Core\Page\Event\BeforePageRenderEvent;
use TYPO3\CMS\Core\Page\AssetCollector;

final class AddDashboardAssets
{
    public function __invoke(BeforePageRenderEvent $event): void
    {
        // Nur im Backend ausführen
        if (!str_starts_with((string)($GLOBALS['TYPO3_REQUEST']->getUri()->getPath() ?? ''), '/typo3/')) {
            return;
        }

        $assetCollector = $event->getAssetCollector();

        $assetCollector->addJavaScript(
            'jobman-dashboard',
            'EXT:jobman/Resources/Public/JavaScript/dashboard.js',
            ['type' => 'module'] // wichtig für ES6
        );
    }
}

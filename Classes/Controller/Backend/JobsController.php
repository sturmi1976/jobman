<?php

namespace Lanius\Jobman\Controller\Backend;

use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Page\AssetCollector;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Site\SiteFinder;

final class JobsController extends ActionController
{

    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly PageRenderer $pageRenderer,
    ) {}


    public function indexAction(): ResponseInterface
    {

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);


        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($this->settings);


        $siteSettings = [];

        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);

        // Wenn dein Modul seitenbezogen ist (z. B. Web-Modul)
        $pageId = (int)($this->request->getQueryParams()['id'] ?? 0);

        if ($pageId > 0) {
            $site = $siteFinder->getSiteByPageId($pageId);
            $siteSettings = $site->getConfiguration()['settings']['jobman'] ?? [];
        }
        /*
$moduleTemplate->assignMultiple([
            'siteSettings',
            $siteSettings
        ]);*/

        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($siteFinder->getSiteByPageId(1));

        return $moduleTemplate->renderResponse('Backend/List');
    }
}

<?php

namespace Lanius\Jobman\Controller\Backend;

use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Page\AssetCollector;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Backend\Routing\UriBuilder;

use Lanius\Jobman\Domain\Repository\JobRepository;


final class JobsController extends ActionController
{

    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
    ) {}

    protected JobRepository $jobRepository;


    public function initializeAction(): void
    {
        $this->jobRepository = GeneralUtility::makeInstance(JobRepository::class);
    }


    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        $assetCollector->addStyleSheet(
            'jobsList',
            'EXT:jobman/Resources/Public/Css/Backend/Jobs-list.css',
        );

        // Holen aller GET-Parameter als Array
        $queryParams = $this->request->getQueryParams();
        if (isset($queryParams['id'])) {
            $folderId = (int)$queryParams['id'];
        } else {
            $folderId = "";
        }

        if ($this->checkFolder($folderId) == false || $folderId == "") {
            $moduleTemplate->assign('no_page_selected', 1);
            return $moduleTemplate->renderResponse('Backend/List');
        }

        $moduleTemplate->assign('createLink', $this->generateNewRecordLink('tx_jobman_domain_model_job', $folderId));
        $moduleTemplate->assign('dashboardLink', $this->generateDashboardLink());
        $moduleTemplate->assign('allJobsLink', $this->generateAllJobsLink());

        return $moduleTemplate->renderResponse('Backend/List');
    }


    public function allJobsAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        $assetCollector->addStyleSheet(
            'jobsList',
            'EXT:jobman/Resources/Public/Css/Backend/Jobs-list.css',
        );

        $assetCollector->addJavaScript(
            'jobman-functions',
            'EXT:jobman/Resources/Public/JavaScript/functions.js',
            ['type' => 'module'] // wichtig für ES6
        );

        $queryParams = $this->request->getQueryParams();
        if (isset($queryParams['id'])) {
            $folderId = (int)$queryParams['id'];
        } else {
            $folderId = "";
        }

        if ($this->checkFolder($folderId) == false || $folderId == "") {
            $moduleTemplate->assign('no_page_selected', 1);
            return $moduleTemplate->renderResponse('Backend/AllJobs');
        }

        // All Jobs
        $jobs = $this->jobRepository->findAllByFolder($folderId);

        $jobsWithLinks = [];

        foreach ($jobs as $job) {
            $jobsWithLinks[] = [
                'job' => $job,
                'editLink' => $this->generateEditLink(
                    $job->getUid(),
                    'tx_jobman_domain_model_job'
                ),
                'deleteLink' => $this->generateDeleteLink(
                    $job->getUid(),
                    'tx_jobman_domain_model_job'
                ),
            ];
        }

        $moduleTemplate->assign('jobs', $jobsWithLinks);

        return $moduleTemplate->renderResponse('Backend/AllJobs');
    }



    public function checkFolder($pageId)
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        $queryBuilder = $connectionPool->getQueryBuilderForTable('pages');

        $record = $queryBuilder
            ->select('uid', 'doktype', 'title')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageId))
            )
            ->fetchAssociative();

        if ($record) {
            // doktype 254 = "Folder", 256 = "SysFolder"
            if ((int)$record['doktype'] === 254 || (int)$record['doktype'] === 256) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }



    public function generateNewRecordLink(string $tableName, $pid): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $newParameters = [
            'edit' => [
                $tableName => [
                    $pid => 'new',
                ],
            ],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];

        return (string)$uriBuilder->buildUriFromRoute('record_edit', $newParameters);
    }



    public function generateEditLink(int $recordUid, string $tableName): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $editParameters = [
            'edit' => [
                $tableName => [
                    $recordUid => 'edit',
                ],
            ],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];

        return (string)$uriBuilder->buildUriFromRoute('record_edit', $editParameters);
    }



    public function generateDeleteLink(int $recordUid, string $tableName): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        $deleteParameters = [
            'cmd' => [
                $tableName => [
                    $recordUid => [
                        'delete' => 1,
                    ],
                ],
            ],
            'redirect' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ];

        return (string)$uriBuilder->buildUriFromRoute('tce_db', $deleteParameters);
    }


    public function generateDashboardLink(): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        return (string)$uriBuilder->buildUriFromRoute('jobman_dashboard');
    }


    public function generateAllJobsLink(): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        return $uriBuilder->buildUriFromRoute('jobman_jobs', [
            'id' => 3,
            'tx_jobman_jobman_jobs' => [
                'action' => 'allJobs'
            ]
        ]);
    }
}

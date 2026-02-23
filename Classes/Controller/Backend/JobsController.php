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
use Lanius\Jobman\Domain\Repository\ApplicationRepository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;



final class JobsController extends ActionController
{

    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
    ) {}

    protected JobRepository $jobRepository;
    protected ApplicationRepository $applicationRepository;


    public function initializeAction(): void
    {
        $this->jobRepository = GeneralUtility::makeInstance(JobRepository::class);
        $this->applicationRepository = GeneralUtility::makeInstance(ApplicationRepository::class);
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
        $moduleTemplate->assign('allJobsLink', $this->generateAllJobsLink($folderId));

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
                'viewCount' => $this->jobRepository->getViewCountForJob($job),
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




    public function applicationsAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);

        $assetCollector->addStyleSheet(
            'jobsList',
            'EXT:jobman/Resources/Public/Css/Backend/Jobs-list.css'
        );

        $assetCollector->addStyleSheet(
            'applications',
            'EXT:jobman/Resources/Public/Css/Backend/Jobs-applications.css'
        );

        // =============================
        // Sorting Logic
        // =============================

        $orderField = $this->request->hasArgument('order')
            ? $this->request->getArgument('order')
            : 'crdate';

        $direction = $this->request->hasArgument('direction')
            ? strtoupper($this->request->getArgument('direction'))
            : 'DESC';

        $allowedFields = ['crdate', 'status', 'name'];

        if (!in_array($orderField, $allowedFields, true)) {
            $orderField = 'crdate';
        }

        $query = $this->applicationRepository->createQuery();

        $query->setOrderings([
            $orderField => $direction === 'ASC'
                ? \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
                : \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING
        ]);

        $applications = $query->execute();

        $toggleDirection = 'ASC';

        if (
            $this->request->hasArgument('order') &&
            $this->request->getArgument('order') === $orderField
        ) {
            $toggleDirection = $direction === 'DESC' ? 'ASC' : 'DESC';
        }


        // =============================
        // Assign Template Variables
        // =============================

        $moduleTemplate->assignMultiple([
            'applications' => $applications,
            'currentOrder' => $orderField,
            'currentDirection' => $direction,
            'toggleDirection' => $toggleDirection
        ]);

        return $moduleTemplate->renderResponse('Backend/Applications');
    }





    public function showApplicationAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);
        $assetCollector->addStyleSheet(
            'jobsList',
            'EXT:jobman/Resources/Public/Css/Backend/Jobs-list.css',
        );
        $assetCollector->addStyleSheet(
            'jobsList',
            'EXT:jobman/Resources/Public/Css/Backend/Jobs-showApplication.css',
        );

        $queryParams = $this->request->getQueryParams();
        if (isset($queryParams['application'])) {
            $application = (int)$queryParams['application'];
        } else {
            $application = "";
        }

        $showApplication = $this->applicationRepository->findByUid($application);

        $moduleTemplate->assign('application', $showApplication);

        return $moduleTemplate->renderResponse('Backend/ShowApplications');
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


    public function generateAllJobsLink($pid): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);

        return $uriBuilder->buildUriFromRoute('jobman_jobs', [
            'id' => $pid,
            'tx_jobman_jobman_jobs' => [
                'action' => 'allJobs'
            ]
        ]);
    }
}

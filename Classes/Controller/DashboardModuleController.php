<?php

namespace Lanius\Jobman\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Context\Context;

final class DashboardModuleController extends ActionController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
    ) {}


    public function indexAction(): ResponseInterface
    {

        // Daten für Dashboard
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_jobman_domain_model_job');

        // Heutiges Datum als Timestamp
        $today = (new \DateTime())->getTimestamp();

        // QueryBuilder
        $queryBuilder = $connection->createQueryBuilder();

        // Anzahl aktiver Jobs (validThrough >= heute)
        $activeJobs = $queryBuilder
            ->count('uid')
            ->from('tx_jobman_domain_model_job')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0),
                $queryBuilder->expr()->eq('sys_language_uid', 0),
            )
            ->fetchOne();


        // Anzahl abgelaufener Jobs
        $expiredJobs = $connection->createQueryBuilder()
            ->count('uid')
            ->from('tx_jobman_domain_model_job')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0),
                $queryBuilder->expr()->eq('sys_language_uid', 0),
                $queryBuilder->expr()->lt('valid_through', $connection->quote(date('Y-m-d')))
            )
            ->fetchOne();


        // Anzahl Bewerbungen gesamt
        $totalApplications = $connection->createQueryBuilder()
            ->count('uid')
            ->from('tx_jobman_domain_model_application')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0),
                //$queryBuilder->expr()->eq('sys_language_uid', 0)
            )
            ->fetchOne();


        // Anzahl Bewerbungen letzte 30 Tage
        $last30DaysApplications = $connection->createQueryBuilder()
            ->count('uid')
            ->from('tx_jobman_domain_model_application')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0),
                $queryBuilder->expr()->gte('crdate', strtotime('-30 days'))
            )
            ->fetchOne();


        // Template Variablen
        $this->view->assignMultiple([
            'activeJobs' => $activeJobs,
            'expiredJobs' => $expiredJobs,
            'applicationsTotal' => $totalApplications,
            'applicationsLast30Days' => $last30DaysApplications,
        ]);



        return $this->htmlResponse();
    }
}

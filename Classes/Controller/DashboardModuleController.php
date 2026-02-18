<?php

namespace Lanius\Jobman\Controller;

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

final class DashboardModuleController extends ActionController
{


    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly PageRenderer $pageRenderer,
    ) {}


    public function indexAction(): ResponseInterface
    {

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);

        $assetCollector->addJavaScript(
            'jobman-charts',
            'EXT:jobman/Resources/Public/JavaScript/charts.js',
            ['type' => 'module'] // wichtig für ES6
        );

        $assetCollector->addJavaScript(
            'jobman-dashboard',
            'EXT:jobman/Resources/Public/JavaScript/dashboard.js',
            ['type' => 'module'] // wichtig für ES6
        );



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



        // =======================================================================


        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_jobman_domain_model_application');

        $queryBuilder = $connection->createQueryBuilder();

        /**
         * Bewerbungen pro Monat (letzte 6 Monate)
         */
        $months = [];
        $counts = [];

        for ($i = 5; $i >= 0; $i--) {
            $start = strtotime(date('Y-m-01 00:00:00', strtotime("-$i months")));
            $end   = strtotime(date('Y-m-t 23:59:59', strtotime("-$i months")));

            $count = $connection->createQueryBuilder()
                ->count('uid')
                ->from('tx_jobman_domain_model_application')
                ->where(
                    $queryBuilder->expr()->eq('deleted', 0),
                    $queryBuilder->expr()->eq('hidden', 0),
                    $queryBuilder->expr()->gte('crdate', $start),
                    $queryBuilder->expr()->lte('crdate', $end)
                )
                ->fetchOne();

            $months[] = date('M Y', $start);
            $counts[] = (int)$count;
        }

        /**
         * Status-Verteilung
         */
        /**
         * Status-Verteilung (mit TCA-Labels)
         */

        // 1️⃣ Status-Mapping aus dem TCA holen
        $tcaItems = $GLOBALS['TCA']['tx_jobman_domain_model_application']['columns']['status']['config']['items'];

        $statusMap = [];

        foreach ($tcaItems as $item) {

            // Skip divider / ungültige Items
            if (!isset($item[1]) || !is_numeric($item[1])) {
                continue;
            }

            $statusValue = (int)$item[1];
            $statusLabel = $item[0];

            // Falls LLL-Label → auflösen
            if (str_starts_with($statusLabel, 'LLL:')) {
                $statusLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate($statusLabel) ?? $statusLabel;
            }

            $statusMap[$statusValue] = $statusLabel;
        }


        // 2️⃣ Status-Werte aus DB holen
        $statusData = $connection->createQueryBuilder()
            ->select('status')
            ->addSelectLiteral('COUNT(uid) as total')
            ->from('tx_jobman_domain_model_application')
            ->where(
                $queryBuilder->expr()->eq('deleted', 0),
                $queryBuilder->expr()->eq('hidden', 0)
            )
            ->groupBy('status')
            ->fetchAllAssociative();

        // 3️⃣ Alle Status zunächst mit 0 initialisieren
        $statusCountsMap = array_fill_keys(array_keys($statusMap), 0);

        // 4️⃣ DB-Werte eintragen
        foreach ($statusData as $row) {
            $statusValue = (int)$row['status'];
            $statusCountsMap[$statusValue] = (int)$row['total'];
        }

        // 5️⃣ Finale Arrays für Chart
        $statusLabels = array_values($statusMap);
        $statusCounts = array_values($statusCountsMap);



        $moduleTemplate->assignMultiple([
            'activeJobs' => $activeJobs,
            'expiredJobs' => $expiredJobs,
            'applicationsTotal' => $totalApplications,
            'applicationsLast30Days' => $last30DaysApplications,
            'chartMonths' => json_encode($months),
            'chartCounts' => json_encode($counts),
            'statusLabels' => json_encode($statusLabels),
            'statusCounts' => json_encode($statusCounts),
        ]);


        return $moduleTemplate->renderResponse('DashboardModule/Index');
    }
}

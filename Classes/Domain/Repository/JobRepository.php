<?php


namespace Lanius\Jobman\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class JobRepository extends Repository
{
    public function getViewCountForJob(\Lanius\Jobman\Domain\Model\Job $job): int
{
    $connection = GeneralUtility::makeInstance(ConnectionPool::class)
        ->getConnectionForTable('tx_jobman_job_views');

    $queryBuilder = $connection->createQueryBuilder();

    $count = $queryBuilder
        ->count('uid')
        ->from('tx_jobman_job_views')
        ->where(
            $queryBuilder->expr()->eq(
                'job',
                $queryBuilder->createNamedParameter($job->getUid())
            )
        )
        ->executeQuery()
        ->fetchOne();

    return (int)$count;
}



    public function findAllActive(int $pid)
    {
        $query = $this->createQuery();

        // Nur Datensätze aus dem angegebenen PID
        $query->matching(
            $query->equals('pid', $pid)
        );

        // Nach sorting sortieren
        $query->setOrderings([
            'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
        ]);

        return $query->execute();
    }




    /**
     * Find all jobs in a specific sysfolder
     *
     * @param int $folderId
     * @return array
     */
    public function findAllByFolder(int $folderId): QueryResultInterface
    {
        $query = $this->createQuery();

        // Filter auf die PID setzen
        $query->matching(
            $query->equals('pid', $folderId)
        );

        // Optional: sortieren nach z.B. title oder uid
        $query->setOrderings(['title' => QueryInterface::ORDER_ASCENDING]);

        // Ausführen und Ergebnisse zurückgeben
        return $query->execute();
    }
}

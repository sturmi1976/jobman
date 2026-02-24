<?php

declare(strict_types=1);

namespace Lanius\Jobman\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class ApplicationRepository extends Repository
{

    public function updateStatus(int $applicationUid, int $status): void
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_jobman_domain_model_application');

        $queryBuilder
            ->update('tx_jobman_domain_model_application')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($applicationUid)
                )
            )
            ->set('status', $status)
            ->executeStatement();
    }
}

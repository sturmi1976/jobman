<?php

declare(strict_types=1);

namespace Lanius\Jobman\Middleware;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class JobViewCounterMiddleware implements MiddlewareInterface
{
    private const TABLE_VIEWS = 'tx_jobman_job_views';
    private const TABLE_JOB = 'tx_jobman_domain_model_job';

    /**
     * Tracking Window (24 Stunden)
     */
    private const TIME_WINDOW = 86400;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pluginParams = $request->getQueryParams()['tx_jobman_pi1'] ?? [];

        $jobUid = (int)($pluginParams['job'] ?? 0);

        if ($jobUid > 0) {
            $ip = GeneralUtility::getIndpEnv('REMOTE_ADDR');
            $this->incrementViewCounter($jobUid, $ip);
        }

        return $handler->handle($request);
    }

    private function incrementViewCounter(int $jobUid, string $ip): void
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);

        $viewsConnection = $connectionPool->getConnectionForTable(self::TABLE_VIEWS);
        $jobsConnection = $connectionPool->getConnectionForTable(self::TABLE_JOB);

        $bucket = (int)floor(time() / self::TIME_WINDOW);
        $ipHash = hash('sha256', $ip);
        $now = time();

        try {

            // Unique Constraint übernimmt Duplicate Schutz
            $viewsConnection->insert(self::TABLE_VIEWS, [
                'job' => $jobUid,
                'ip_hash' => $ipHash,
                'bucket' => $bucket,
                'tstamp' => $now,
            ]);

            
            $jobsConnection->executeStatement(
                'UPDATE ' . self::TABLE_JOB .
                ' SET views = views + 1
                WHERE uid = ?
                AND deleted = 0',
                [$jobUid]
            );

        } catch (UniqueConstraintViolationException $e) {
            
        }
    }
}

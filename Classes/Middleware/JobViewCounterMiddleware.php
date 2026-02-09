<?php

declare(strict_types=1);

namespace Lanius\Jobman\Middleware;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JobViewCounterMiddleware implements MiddlewareInterface
{
    /**
     * Zeitraum in Sekunden, z.B. 24h = 86400
     */
    private const TIME_WINDOW = 86400;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pluginParams = $request->getQueryParams()['tx_jobman_pi1'] ?? [];
        $jobUid = (int)($pluginParams['job'] ?? 0);

        if ($jobUid > 0) {
            $ip = $this->getClientIp($request);
            $this->incrementViewCounter($jobUid, $ip);
        }

        return $handler->handle($request);
    }

    private function incrementViewCounter(int $jobUid, string $ip): void
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $viewsConnection = $connectionPool->getConnectionForTable('tx_jobman_job_views');
        $jobsConnection = $connectionPool->getConnectionForTable('tx_jobman_domain_model_job');

        $now = time();
        $currentDay = (int)floor($now / self::TIME_WINDOW); // Tagesfenster für UNIQUE-Key

        // Transaktion starten (atomic)
        $viewsConnection->beginTransaction();
        try {
            // Prüfen, ob schon ein View für diese IP im aktuellen Tagesfenster existiert
            $existing = $viewsConnection->fetchOne(
                'SELECT uid FROM tx_jobman_job_views WHERE job = ? AND ip = ? AND day = ? FOR UPDATE',
                [$jobUid, $ip, $currentDay]
            );

            if ($existing === false) {
                // Neuer Eintrag in Job-Views
                $viewsConnection->insert(
                    'tx_jobman_job_views',
                    [
                        'job' => $jobUid,
                        'ip' => $ip,
                        'tstamp' => $now,
                        'day' => $currentDay
                    ]
                );

                // Gesamtviews hochzählen
                $jobsConnection->executeStatement(
                    'UPDATE tx_jobman_domain_model_job SET views = views + 1 WHERE uid = ? AND deleted = 0',
                    [$jobUid]
                );
            }

            $viewsConnection->commit();
        } catch (\Throwable $e) {
            $viewsConnection->rollBack();
            // Optional: Logger, damit man sieht, wenn etwas schiefgeht
        }
    }

    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        return $serverParams['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

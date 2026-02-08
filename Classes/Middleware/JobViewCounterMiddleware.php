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
    // Zeitraum in Sekunden, z.B. 24h = 86400
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
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_jobman_job_views');

        $now = time();

        // Prüfen, ob in TIME_WINDOW schon ein View für diese IP existiert
        $existing = $connection->fetchOne(
            'SELECT uid FROM tx_jobman_job_views WHERE job = ? AND ip = ? AND tstamp > ?',
            [$jobUid, $ip, $now - self::TIME_WINDOW]
        );

        if ($existing === false) {
            // Eintragen
            $connection->insert(
                'tx_jobman_job_views',
                [
                    'job' => $jobUid,
                    'ip' => $ip,
                    'tstamp' => $now
                ]
            );

            // Gesamtviews in Haupttabelle erhöhen
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('tx_jobman_domain_model_job');

            $connection->executeStatement(
                'UPDATE tx_jobman_domain_model_job SET views = views + 1 WHERE uid = ? AND deleted = 0',
                [$jobUid]
            );
        }
    }

    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();
        $ip = $serverParams['REMOTE_ADDR'] ?? '0.0.0.0';
        return $ip;
    }
}

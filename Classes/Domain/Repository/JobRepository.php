<?php


namespace Lanius\Jobman\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class JobRepository extends Repository
{
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
}

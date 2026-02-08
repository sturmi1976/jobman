<?php


namespace Lanius\Jobman\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Lanius\Jobman\Domain\Repository\JobRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Site\SiteFinder;
use \TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class JobController extends ActionController
{

    public function __construct(
        private readonly JobRepository $jobRepository
    ) {}




    public function listAction(): ResponseInterface
    {
        $language = $this->request->getAttribute('language');
        /** @var Locale $locale */
        $locale = $language->getLocale();
        $languageKey = $locale->getLanguageCode();

        //\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($languageKey);

        $jobs = $this->jobRepository->findAllActive((int)$this->settings['sysFolder']);

        $listView = $this->settings['display'] ?? 'accordion';
        $accordionType = $this->settings['accordionType'] ?? 'custom';

        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);


        if ($listView === 'accordion') {
            if ($accordionType === 'custom') {
                $assetCollector->addStyleSheet(
                    'jobman-accordion',
                    'EXT:jobman/Resources/Public/Css/job-accordion.css'
                );
                $assetCollector->addJavaScript(
                    'jobman-accordion',
                    'EXT:jobman/Resources/Public/JavaScript/job-accordion.js'
                );
            }
        }

        $this->view->assignMultiple([
            'jobs' => $jobs,
            'listView' => $listView,
            'accordionType' => $accordionType,
        ]);


        return $this->htmlResponse();
    }

    public function showAction(\Lanius\Jobman\Domain\Model\Job $job): ResponseInterface
    {
        $this->view->assign('job', $job);


        return $this->htmlResponse();
    }
}

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
use TYPO3\CMS\Core\Page\PageRenderer;

use TYPO3\CMS\Core\Database\ConnectionPool;

use Lanius\Jobman\PageTitle\TitleTag;

class JobController extends ActionController
{

    public function __construct(
        protected JobRepository $jobRepository,
        protected TitleTag $titleProvider
    ) {}




    public function listAction(): ResponseInterface
    {
        $language = $this->request->getAttribute('language');
        /** @var Locale $locale */
        $locale = $language->getLocale();
        $languageKey = $locale->getLanguageCode();

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

        if ($listView === 'list') {
            $assetCollector->addStyleSheet(
                'jobman-list',
                'EXT:jobman/Resources/Public/Css/job-list.css'
            );
        }

        if ($listView === 'tiles') {
            $assetCollector->addStyleSheet(
                'jobman-list',
                'EXT:jobman/Resources/Public/Css/job-tiles.css'
            );
        }

        $this->view->assignMultiple([
            'jobs' => $jobs,
            'listView' => $listView,
            'accordionType' => $accordionType,
            'languageKey' => $languageKey,
        ]);


        return $this->htmlResponse();
    }



    public function showAction(\Lanius\Jobman\Domain\Model\Job $job): ResponseInterface
    {
        $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);

        $assetCollector->addStyleSheet(
            'jobman-detail',
            'EXT:jobman/Resources/Public/Css/job-detail.css'
        );

        // Title Tag for detail pages
        $this->titleProvider->setTitle(htmlspecialchars($job->getTitle()));


        // --- JSON-LD for Google Jobs ---
        $structuredData = [
            "@context" => "https://schema.org/",
            "@type" => "JobPosting",
            "title" => $job->getTitle(),
            "description" => strip_tags($job->getDescription()),
            "datePosted" => date('c', $job->getTstamp()),
            "validThrough" => $job->getValidThrough() ? date('c', $job->getValidThrough()) : null,
            "employmentType" => $job->getEmploymentType(),
            "hiringOrganization" => [
                "@type" => "Organization",
                "name" => $job->getSdCompany(),
            ],
            "jobLocation" => [
                "@type" => "Place",
                "address" => [
                    "@type" => "PostalAddress",
                    "streetAddress" => $job->getSdStreet(),
                    "postalCode" => $job->getSdPostalcode(),
                    "addressLocality" => $job->getSdCity(),
                    "addressRegion" => $job->getSdRegion(),
                    "addressCountry" => $job->getSdCountry(),
                ],
            ],
            "baseSalary" => [
                "@type" => "MonetaryAmount",
                "currency" => "EUR",
                "value" => [
                    "@type" => "QuantitativeValue",
                    "value" => floatval(str_replace(['€', ','], ['', '.'], $job->getSalary())),
                    "unitText" => "YEAR"
                ]
            ],
        ];




        $contactAddress = null;

        if ($job->getAddressMode() === 'tt_address' && $job->getAddressTt() > 0) {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('tt_address');

            $row = $connection->fetchAssociative(
                'SELECT * 
             FROM tt_address 
             WHERE uid = ? AND deleted = 0 AND hidden = 0',
                [$job->getAddressTt()]
            );

            if ($row) {
                $contactAddress = [
                    'type' => 'tt_address',
                    'company' => $row['company'] ?? '',
                    'name' => $row['name'] ?? '',
                    'address' => $row['address'] ?? '',
                    'zip' => $row['zip'] ?? '',
                    'city' => $row['city'] ?? '',
                    'region' => $row['region'] ?? '',
                    'country' => $row['country'] ?? '',
                    'email' => $row['email'] ?? '',
                    'www' => $row['www'] ?? '',
                ];
            }
        }

        if ($job->getAddressMode() === 'manual' && trim($job->getAddressManual()) !== '') {
            $contactAddress = [
                'type' => 'manual',
                'html' => $job->getAddressManual(),
            ];
        }

        // PageRenderer
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        $pageRenderer->addHeaderData('<script type="application/ld+json">' . json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>');

        $this->view->assignMultiple([
            'job' => $job,
            'contactAddress' => $contactAddress,
        ]);



        return $this->htmlResponse();
    }
}

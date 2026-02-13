<?php


namespace Lanius\Jobman\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Lanius\Jobman\Domain\Repository\JobRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;


use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Site\SiteFinder;
use \TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Page\PageRenderer;

use TYPO3\CMS\Core\Database\ConnectionPool;

use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\MailerInterface;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;

use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;

use Lanius\Jobman\Domain\Model\Application;
use Lanius\Jobman\Domain\Repository\ApplicationRepository;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Core\Resource\FileReference;

use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

use Lanius\Jobman\PageTitle\TitleTag;

use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Core\Resource\FileReference as CoreFileReference;

class JobController extends ActionController
{
    //protected ?TitleTag $titleProvider = null;


    protected JobRepository $jobRepository;
    protected ApplicationRepository $applicationRepository;
    protected PersistenceManagerInterface $persistenceManager;
    protected TitleTag $titleProvider;

    public function initializeAction(): void
    {
        $this->jobRepository = GeneralUtility::makeInstance(JobRepository::class);
        $this->applicationRepository = GeneralUtility::makeInstance(ApplicationRepository::class);
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManagerInterface::class);
        $this->titleProvider = GeneralUtility::makeInstance(TitleTag::class);
    }



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



    public function applicationAction(\Lanius\Jobman\Domain\Model\Job $job): ResponseInterface
    {
        $language = $this->request->getAttribute('language');
        /** @var Locale $locale */
        $locale = $language->getLocale();
        $languageKey = $locale->getLanguageCode();

        $submit_text = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('submit_text', 'jobman', [], $languageKey);

        // Title Tag for application
        $this->titleProvider->setTitle('Bewerbung für: ' . htmlspecialchars($job->getTitle()));

        $this->view->assign('job', $job);
        $this->view->assign('submit_text', $submit_text);

        return $this->htmlResponse();
    }



    public function submitApplicationAction(\Lanius\Jobman\Domain\Model\Job $job): ResponseInterface
    {
        $requestData = $this->request->getArguments();

        if (!isset($requestData['submit'])) {
            return $this->redirect('show', 'Job', null, ['job' => $job]);
        }

        // ------------------------------
        // 1️⃣ Formular-Daten
        // ------------------------------
        $name = htmlspecialchars($requestData['name']);
        $mail = htmlspecialchars($requestData['email']);
        $message = nl2br(htmlspecialchars($requestData['message']));


        $language = $this->request->getAttribute('language');
        /** @var Locale $locale */
        $locale = $language->getLocale();
        $languageKey = $locale->getLanguageCode();

        if (isset($this->settings['applicationPID'])) {
            $pid = $this->settings['applicationPID'];
        } else {
            $pid = $this->settings['sysFolder'];
        }

        $application = new \Lanius\Jobman\Domain\Model\Application();
        $application->setPid((int)$pid);
        $application->setJob($job);
        $application->setName($name);
        $application->setEmail($mail);
        $application->setMessage($message);

        $uploadedFalFiles = $this->uploadApplicationFiles($name);

        $fileStorage = new ObjectStorage();

        foreach ($uploadedFalFiles as $coreFileReference) {
            $extbaseFileReference = GeneralUtility::makeInstance(ExtbaseFileReference::class);
            $extbaseFileReference->setOriginalResource($coreFileReference);
            $fileStorage->attach($extbaseFileReference);
        }

        $application->setFiles($fileStorage);

        $application->setFiles($fileStorage);

        $this->applicationRepository->add($application);
        $this->persistenceManager->persistAll();

        // ------------------------------
        // 4️⃣ E-Mail versenden
        // ------------------------------
        $subject = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mailSubject', 'jobman', [], $languageKey);
        $subject .= $job->getTitle();

        $subject_label = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('mailSubject', 'jobman', [], $languageKey);
        $applicationFrom = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('applicationFrom', 'jobman', [], $languageKey);
        $messageLabel = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('message_label', 'jobman', [], $languageKey);

        $email = new \TYPO3\CMS\Core\Mail\FluidEmail();
        $email
            ->to($job->getEmail())
            ->from(new \Symfony\Component\Mime\Address($mail, $name))
            //->subject($subject)
            ->format(\TYPO3\CMS\Core\Mail\FluidEmail::FORMAT_HTML)
            ->setTemplate('Application')
            ->assign('name', $name)
            ->assign('email', $mail)
            ->assign('message', $message)
            ->assign('subject_label', $subject_label)
            ->assign('applicationFrom', $applicationFrom)
            ->assign('message_label', $messageLabel)
            ->assign('job', $job);

        foreach ($uploadedFalFiles as $coreFileReference) {
            $email->attachFromPath($coreFileReference->getForLocalProcessing(false));
        }

        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Mail\MailerInterface::class)->send($email);

        // ------------------------------
        // 5️⃣ Erfolgsmeldung & Redirect
        // ------------------------------
        $this->addFlashMessage(
            'Application successfully submitted.',
            'Success',
            \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::OK
        );

        //return $this->redirect('show', 'Job', null, ['job' => $job]);
        return $this->redirect('success', 'Job', null, ['job' => $job]);
    }


    /**
     * Upload Function: speichert Dateien in FAL unter fileadmin/bewerbungen/{ApplicantName}
     *
     * @param string $name
     * @return CoreFileReference[]
     */
    public function uploadApplicationFiles(string $name): array
    {
        $savedFileReferences = [];

        /** @var StorageRepository $storageRepository */
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storage = $storageRepository->findByUid((int)$this->settings['applicationStorage']); // FAL Storage UID

        $baseFolderName = 'bewerbungen';
        $baseFolder = $storage->hasFolder($baseFolderName)
            ? $storage->getFolder($baseFolderName)
            : $storage->createFolder($baseFolderName);

        $applicantName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        $applicantFolder = $baseFolder->hasFolder($applicantName)
            ? $baseFolder->getSubfolder($applicantName)
            : $storage->createFolder($applicantName, $baseFolder);

        $uploadedFiles = $this->request->getUploadedFiles();

        if (!empty($uploadedFiles['file'])) {
            foreach ($uploadedFiles['file'] as $uploadedFile) {
                if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                    // Datei in FAL speichern
                    $file = $storage->addUploadedFile(
                        $uploadedFile,
                        $applicantFolder,
                        $uploadedFile->getClientFilename(),
                        DuplicationBehavior::RENAME
                    );

                    // FAL FileReference erzeugen
                    $fileReference = GeneralUtility::makeInstance(CoreFileReference::class, [
                        'uid_local' => $file->getUid(),
                        'uid_foreign' => 0,
                        'tablenames' => '',
                        'fieldname' => '',
                        'pid' => $file->getStorage()->getUid(),
                        'table_local' => 'sys_file',
                    ]);

                    $savedFileReferences[] = $fileReference;
                }
            }
        }

        return $savedFileReferences;
    }



    public function successAction(\Lanius\Jobman\Domain\Model\Job $job): ResponseInterface
    {
        $language = $this->request->getAttribute('language');
        /** @var Locale $locale */
        $locale = $language->getLocale();
        $languageKey = $locale->getLanguageCode();

        $this->titleProvider->setTitle(\TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('success_title', 'jobman', [], $languageKey));

        return $this->htmlResponse();
    }
}

<?php

namespace Lanius\Jobman\Controller;

use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Psr\Http\Message\ResponseInterface;

final class DashboardModuleController extends ActionController
{
    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
    ) {}


    public function indexAction(): ResponseInterface
    {
        $this->view->assign('message', 'Welcome to the Job Manager Dashboard!');

        return $this->htmlResponse();
    }
}

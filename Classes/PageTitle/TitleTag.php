<?php

declare(strict_types=1);

namespace Lanius\Jobman\PageTitle;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;

final class TitleTag extends AbstractPageTitleProvider
{
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}

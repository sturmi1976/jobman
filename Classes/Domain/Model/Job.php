<?php


namespace Lanius\Jobman\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Job extends AbstractEntity
{
    protected string $title = '';
    protected string $description = '';

    protected string $location = '';
    protected string $employmentType = '';
    protected string $salary = '';
    protected bool $remote = false;
    protected ?\DateTime $validThrough = null;

    public function getTitle(): string
    {
        return $this->title;
    }



    public function getLocation(): string
    {
        return $this->location;
    }



    public function getEmploymentType(): string
    {
        return $this->employmentType;
    }


    public function isRemote(): bool
    {
        return $this->remote;
    }

    public function setRemote(bool $remote): void
    {
        $this->remote = $remote;
    }


    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSalary(): string
    {
        return $this->salary;
    }
}

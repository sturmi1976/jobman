<?php

declare(strict_types=1);

namespace Lanius\Jobman\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

class Application extends AbstractEntity
{
    protected string $name = '';
    protected string $email = '';
    protected string $message = '';

    protected ?Job $job = null;

    /**
     * @var ObjectStorage<FileReference>
     */
    protected ObjectStorage $files;

    public function __construct()
    {
        $this->files = new ObjectStorage();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): void
    {
        $this->job = $job;
    }

    public function addFile(FileReference $file): void
    {
        $this->files->attach($file);
    }

    public function getFiles(): ObjectStorage
    {
        return $this->files;
    }

    /**
     * Set files
     *
     * @param ObjectStorage<FileReference> $files
     */
    public function setFiles(ObjectStorage $files): void
    {
        $this->files = $files;
    }
}

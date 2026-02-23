<?php


namespace Lanius\Jobman\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Job extends AbstractEntity
{
    protected string $title = '';
    protected string $sdCompany = '';
    protected string $description = '';

    protected string $location = '';
    protected string $employmentType = '';
    protected string $salary = '';
    protected bool $remote = false;
    //protected ?\DateTime $validThrough = null;
    protected int $crdate = 0;
    protected int $tstamp = 0;
    protected int $validThrough = 0;

    protected ?string $sdStreet = null;
    protected ?string $sdPostalcode = null;
    protected ?string $sdCity = null;
    protected string $sdCountry = 'DE';
    protected ?string $sdRegion = null;

    protected string $addressMode = '';
    protected int $addressTt = 0;
    protected string $addressManual = '';

    protected string $remoteType = '';

    protected bool $showButton;

    protected string $buttonType = '';

    protected string $externLink = '';
    protected string $email = '';

    /**
 * @var int
 */
protected int $viewCount = 0;

public function getViewCount(): int
{
    return $this->viewCount;
}

public function setViewCount(int $viewCount): void
{
    $this->viewCount = $viewCount;
}


    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /* ==========================
       Show Button
       ========================== */

    public function getShowButton(): bool
    {
        return $this->showButton;
    }

    public function setShowButton(bool $showButton): void
    {
        $this->showButton = $showButton;
    }

    /* ==========================
       Button Type
       ========================== */

    public function getButtonType(): string
    {
        return $this->buttonType;
    }

    public function setButtonType(string $buttonType): void
    {
        $this->buttonType = $buttonType;
    }

    /* ==========================
       External Link
       ========================== */

    public function getExternLink(): string
    {
        return $this->externLink;
    }

    public function setExternLink(string $externLink): void
    {
        $this->externLink = $externLink;
    }

    public function getRemoteType(): string
    {
        return $this->remoteType;
    }

    public function setRemoteType(string $remoteType): void
    {
        $this->remoteType = $remoteType;
    }


    public function getAddressMode(): string
    {
        return $this->addressMode;
    }

    public function getAddressTt(): int
    {
        return $this->addressTt;
    }

    public function getAddressManual(): string
    {
        return $this->addressManual;
    }


    public function getSdRegion(): ?string
    {
        return $this->sdRegion;
    }

    public function setSdRegion(?string $sdRegion): void
    {
        $this->sdRegion = $sdRegion;
    }



    public function getSdCompany(): ?string
    {
        return $this->sdCompany;
    }

    public function setSdCompany(?string $sdCompany): void
    {
        $this->sdCompany = $sdCompany;
    }


    public function getSdStreet(): ?string
    {
        return $this->sdStreet;
    }

    public function setSdStreet(?string $sdStreet): void
    {
        $this->sdStreet = $sdStreet;
    }

    public function getSdPostalcode(): ?string
    {
        return $this->sdPostalcode;
    }

    public function setSdPostalcode(?string $sdPostalcode): void
    {
        $this->sdPostalcode = $sdPostalcode;
    }

    public function getSdCity(): ?string
    {
        return $this->sdCity;
    }

    public function setSdCity(?string $sdCity): void
    {
        $this->sdCity = $sdCity;
    }

    public function getSdCountry(): string
    {
        return $this->sdCountry;
    }

    public function setSdCountry(string $sdCountry): void
    {
        $this->sdCountry = $sdCountry;
    }


    public function getValidThrough(): ?int
    {
        return $this->validThrough ?: null;
    }

    public function setValidThrough(int $validThrough): void
    {
        $this->validThrough = $validThrough;
    }


    /**
     * @return int
     */
    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    /**
     * @param int $tstamp
     */
    public function setTstamp(int $tstamp): void
    {
        $this->tstamp = $tstamp;
    }

    /**
     * @return int
     */
    public function getCrdate(): int
    {
        return $this->crdate;
    }

    /**
     * @param int $crdate
     */
    public function setCrdate(int $crdate): void
    {
        $this->crdate = $crdate;
    }

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

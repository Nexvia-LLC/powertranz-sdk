<?php

declare(strict_types=1);

namespace PowerTranz\Request\Beans;

use PowerTranz\Exceptions\ValidationException;

/**
 * Recurring payment schedule parameters (embedded in {@see ExtendedRequestData}).
 */
class RecurringRequestData
{
    private ?string $startDate  = null;
    private ?string $frequency  = null;
    private ?string $expiryDate = null;
    private ?bool   $isManaged  = null;

    public function setStartDate(string $v): void  { $this->startDate = $v; }
    public function setFrequency(string $v): void  { $this->frequency = $v; }
    public function setExpiryDate(string $v): void { $this->expiryDate = $v; }
    public function setManaged(bool $v): void      { $this->isManaged = $v; }

    public function getStartDate(): ?string  { return $this->startDate; }
    public function getFrequency(): ?string  { return $this->frequency; }
    public function getExpiryDate(): ?string { return $this->expiryDate; }
    public function isManaged(): ?bool       { return $this->isManaged; }

    public function toArray(): array
    {
        $data = [];
        if ($this->startDate !== null)  $data['StartDate']  = $this->startDate;
        if ($this->frequency !== null)  $data['Frequency']  = $this->frequency;
        if ($this->expiryDate !== null) $data['ExpiryDate'] = $this->expiryDate;
        if ($this->isManaged !== null)  $data['Managed']    = $this->isManaged;
        return $data;
    }
}

/**
 * Hosted Payment Page configuration.
 *
 * @important PageSet MUST be prefixed with 'PTZ/' — enforced at construction.
 */
class HostedPageRequestData
{
    public function __construct(
        private string $pageSet,
        private string $pageName,
    ) {
        if (!str_starts_with($pageSet, 'PTZ/')) {
            throw new ValidationException(
                'HPP PageSet MUST start with "PTZ/" (e.g., "PTZ/YourPageSet"). ' .
                'Missing prefix will cause the hosted page to fail to load.'
            );
        }
    }

    public function getPageSet(): string  { return $this->pageSet; }
    public function getPageName(): string { return $this->pageName; }

    public function toArray(): array
    {
        return [
            'PageSet'  => $this->pageSet,
            'PageName' => $this->pageName,
        ];
    }
}

/**
 * Account information for enhanced 3-D Secure 2 authentication.
 */
class AccountInfoRequestData
{
    private ?string $accountAgeIndicator       = null;
    private ?string $accountChangeIndicator    = null;
    private ?string $accountChangeDate         = null;
    private ?string $accountCreationDate       = null;
    private ?string $passwordChangeIndicator   = null;
    private ?string $passwordChangeDate        = null;
    private ?string $shippingAddressUsageDate  = null;
    private ?string $shippingAddressUsageIndicator = null;
    private ?string $transactionCountDay       = null;
    private ?string $transactionCountYear      = null;
    private ?string $paymentAccountAge         = null;
    private ?string $paymentAccountIndicator   = null;
    private ?bool   $suspiciousActivity        = null;

    public function setAccountAgeIndicator(string $v): void           { $this->accountAgeIndicator = $v; }
    public function setAccountChangeIndicator(string $v): void        { $this->accountChangeIndicator = $v; }
    public function setAccountChangeDate(string $v): void             { $this->accountChangeDate = $v; }
    public function setAccountCreationDate(string $v): void           { $this->accountCreationDate = $v; }
    public function setPasswordChangeIndicator(string $v): void       { $this->passwordChangeIndicator = $v; }
    public function setPasswordChangeDate(string $v): void            { $this->passwordChangeDate = $v; }
    public function setShippingAddressUsageDate(string $v): void      { $this->shippingAddressUsageDate = $v; }
    public function setShippingAddressUsageIndicator(string $v): void { $this->shippingAddressUsageIndicator = $v; }
    public function setTransactionCountDay(string $v): void           { $this->transactionCountDay = $v; }
    public function setTransactionCountYear(string $v): void          { $this->transactionCountYear = $v; }
    public function setPaymentAccountAge(string $v): void             { $this->paymentAccountAge = $v; }
    public function setPaymentAccountIndicator(string $v): void       { $this->paymentAccountIndicator = $v; }
    public function setSuspiciousActivity(bool $v): void              { $this->suspiciousActivity = $v; }

    public function toArray(): array
    {
        $data = [];
        foreach (get_object_vars($this) as $k => $v) {
            if ($v !== null) {
                $data[ucfirst($k)] = $v;
            }
        }
        return $data;
    }
}

/**
 * Extended request payload: secondaryAddress, customData, level2/level3, 3DS, recurring,
 * browserInfo, merchantResponseUrl (callback), hostedPage, etc.
 */
class ExtendedRequestData
{
    private ?Address               $secondaryAddress = null;
    private mixed                  $customData       = null;
    private mixed                  $level2CustomData = null;
    private mixed                  $level3CustomData = null;
    private ?ThreeDSecureRequestData $threeDSecure   = null;
    private ?RecurringRequestData  $recurring        = null;
    private ?BrowserInfoData       $browserInfo      = null;
    private ?string                $merchantResponseUrl = null;
    private ?HostedPageRequestData $hostedPage       = null;

    public function setSecondaryAddress(Address $v): void              { $this->secondaryAddress = $v; }
    public function setCustomData(mixed $v): void                      { $this->customData = $v; }
    public function setLevel2CustomData(mixed $v): void                { $this->level2CustomData = $v; }
    public function setLevel3CustomData(mixed $v): void                { $this->level3CustomData = $v; }
    public function setThreeDSecure(ThreeDSecureRequestData $v): void  { $this->threeDSecure = $v; }
    public function setRecurring(RecurringRequestData $v): void        { $this->recurring = $v; }
    public function setBrowserInfo(BrowserInfoData $v): void           { $this->browserInfo = $v; }
    public function setMerchantResponseUrl(string $v): void            { $this->merchantResponseUrl = $v; }
    public function setHostedPage(HostedPageRequestData $v): void      { $this->hostedPage = $v; }

    public function getThreeDSecure(): ?ThreeDSecureRequestData  { return $this->threeDSecure; }
    public function getHostedPage(): ?HostedPageRequestData      { return $this->hostedPage; }
    public function getMerchantResponseUrl(): ?string            { return $this->merchantResponseUrl; }
    public function getBrowserInfo(): ?BrowserInfoData           { return $this->browserInfo; }
    public function getRecurring(): ?RecurringRequestData        { return $this->recurring; }

    public function toArray(): array
    {
        $data = [];
        if ($this->merchantResponseUrl !== null) $data['MerchantResponseUrl'] = $this->merchantResponseUrl;
        if ($this->threeDSecure !== null)        $data['ThreeDSecure']        = $this->threeDSecure->toArray();
        if ($this->hostedPage !== null)          $data['HostedPage']          = $this->hostedPage->toArray();
        if ($this->browserInfo !== null)         $data['BrowserInfo']         = $this->browserInfo->toArray();
        if ($this->recurring !== null)           $data['Recurring']           = $this->recurring->toArray();
        if ($this->secondaryAddress !== null)    $data['SecondaryAddress']    = $this->secondaryAddress->toArray();
        if ($this->customData !== null)          $data['CustomData']          = $this->customData;
        if ($this->level2CustomData !== null)    $data['Level2CustomData']    = $this->level2CustomData;
        if ($this->level3CustomData !== null)    $data['Level3CustomData']    = $this->level3CustomData;
        return $data;
    }
}

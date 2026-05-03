<?php

declare(strict_types=1);

namespace PowerTranz\Request;

use PowerTranz\Request\Beans\Address;
use PowerTranz\Request\Beans\ExtendedRequestData;
use PowerTranz\Request\Beans\Source;
use PowerTranz\Exceptions\ValidationException;
use Ramsey\Uuid\Uuid;

/**
 * Base class for all financial and non-financial transaction requests.
 *
 * Contains fields shared by SaleRequest, AuthRequest, and NonfinancialRequest.
 */
abstract class BaseTransactionRequest
{
    protected string  $transactionIdentifier;
    protected float   $totalAmount;
    protected string  $currencyCode;
    protected ?float  $tipAmount                  = null;
    protected ?float  $taxAmount                  = null;
    protected ?float  $otherAmount                = null;
    protected ?string $localTime                   = null;
    protected ?string $localDate                   = null;
    protected bool    $threeDSecure                = false;
    protected bool    $fraudCheck                  = false;
    protected bool    $addressVerification         = false;
    protected bool    $addressMatch                = false;
    protected ?bool   $binCheck                    = null;
    protected ?bool   $tokenize                    = null;
    protected ?bool   $recurringInitial            = null;
    protected ?bool   $recurring                   = null;
    protected ?bool   $cardOnFile                  = null;
    protected ?bool   $accountVerification         = null;
    protected ?Source $source                      = null;
    protected ?string $terminalId                  = null;
    protected ?string $terminalCode                = null;
    protected ?string $terminalSerialNumber        = null;
    protected ?string $externalIdentifier          = null;
    protected ?string $externalBatchIdentifier     = null;
    protected ?string $externalGroupIdentifier     = null;
    protected ?string $orderIdentifier             = null;
    protected ?string $email                       = null;
    protected ?string $phoneNumber                 = null;
    protected ?Address $billingAddress             = null;
    protected ?Address $shippingAddress            = null;
    protected ?ExtendedRequestData $extendedData   = null;

    public function __construct(float $totalAmount, string $currencyCode)
    {
        $this->transactionIdentifier = Uuid::uuid4()->toString();
        $this->totalAmount           = $totalAmount;
        $this->currencyCode          = $currencyCode;
        $this->orderIdentifier       = 'ORD-' . strtoupper(substr(Uuid::uuid4()->toString(), 0, 8));
        $this->validateBase();
    }

    // -------------------------------------------------------------------------
    // Setters
    // -------------------------------------------------------------------------
    public function setTransactionIdentifier(string $v): static  { $this->transactionIdentifier = $v; return $this; }
    public function setOrderIdentifier(string $v): static        { $this->orderIdentifier = $v; return $this; }
    public function setTipAmount(float $v): static               { $this->tipAmount = $v; return $this; }
    public function setTaxAmount(float $v): static               { $this->taxAmount = $v; return $this; }
    public function setOtherAmount(float $v): static             { $this->otherAmount = $v; return $this; }
    public function setLocalTime(string $v): static              { $this->localTime = $v; return $this; }
    public function setLocalDate(string $v): static              { $this->localDate = $v; return $this; }
    public function setThreeDSecure(bool $v): static             { $this->threeDSecure = $v; return $this; }
    public function setFraudCheck(bool $v): static               { $this->fraudCheck = $v; return $this; }
    public function setAddressVerification(bool $v): static      { $this->addressVerification = $v; return $this; }
    public function setAddressMatch(bool $v): static             { $this->addressMatch = $v; return $this; }
    public function setBinCheck(bool $v): static                 { $this->binCheck = $v; return $this; }
    public function setTokenize(bool $v): static                 { $this->tokenize = $v; return $this; }
    public function setRecurringInitial(bool $v): static         { $this->recurringInitial = $v; return $this; }
    public function setRecurring(bool $v): static                { $this->recurring = $v; return $this; }
    public function setCardOnFile(bool $v): static               { $this->cardOnFile = $v; return $this; }
    public function setAccountVerification(bool $v): static      { $this->accountVerification = $v; return $this; }
    public function setSource(Source $v): static                 { $this->source = $v; return $this; }
    public function setTerminalId(string $v): static             { $this->terminalId = $v; return $this; }
    public function setTerminalCode(string $v): static           { $this->terminalCode = $v; return $this; }
    public function setTerminalSerialNumber(string $v): static   { $this->terminalSerialNumber = $v; return $this; }
    public function setExternalIdentifier(string $v): static     { $this->externalIdentifier = $v; return $this; }
    public function setExternalBatchIdentifier(string $v): static { $this->externalBatchIdentifier = $v; return $this; }
    public function setExternalGroupIdentifier(string $v): static { $this->externalGroupIdentifier = $v; return $this; }
    public function setEmail(string $v): static                  { $this->email = $v; return $this; }
    public function setPhoneNumber(string $v): static            { $this->phoneNumber = $v; return $this; }
    public function setBillingAddress(Address $v): static        { $this->billingAddress = $v; return $this; }
    public function setShippingAddress(Address $v): static       { $this->shippingAddress = $v; return $this; }
    public function setExtendedData(ExtendedRequestData $v): static { $this->extendedData = $v; return $this; }

    // -------------------------------------------------------------------------
    // Getters
    // -------------------------------------------------------------------------
    public function getTransactionIdentifier(): string    { return $this->transactionIdentifier; }
    public function getTotalAmount(): float               { return $this->totalAmount; }
    public function getCurrencyCode(): string             { return $this->currencyCode; }
    public function getSource(): ?Source                  { return $this->source; }
    public function getExtendedData(): ?ExtendedRequestData { return $this->extendedData; }
    public function isThreeDSecure(): bool                { return $this->threeDSecure; }
    public function isFraudCheck(): bool                  { return $this->fraudCheck; }

    // -------------------------------------------------------------------------
    // Serialization
    // -------------------------------------------------------------------------
    public function toArray(): array
    {
        $data = [
            'TransactionIdentifier' => $this->transactionIdentifier,
            'TotalAmount'           => $this->totalAmount,
            'CurrencyCode'          => $this->currencyCode,
            'ThreeDSecure'          => $this->threeDSecure,
            'AddressMatch'          => $this->addressMatch,
        ];

        if ($this->tipAmount !== null)              $data['TipAmount']               = $this->tipAmount;
        if ($this->taxAmount !== null)              $data['TaxAmount']               = $this->taxAmount;
        if ($this->otherAmount !== null)            $data['OtherAmount']             = $this->otherAmount;
        if ($this->localTime !== null)              $data['LocalTime']               = $this->localTime;
        if ($this->localDate !== null)              $data['LocalDate']               = $this->localDate;
        if ($this->fraudCheck)                      $data['FraudCheck']              = true;
        if ($this->addressVerification)             $data['AddressVerification']     = true;
        if ($this->binCheck !== null)               $data['BinCheck']                = $this->binCheck;
        if ($this->tokenize !== null)               $data['Tokenize']                = $this->tokenize;
        if ($this->recurringInitial !== null)       $data['RecurringInitial']        = $this->recurringInitial;
        if ($this->recurring !== null)              $data['Recurring']               = $this->recurring;
        if ($this->cardOnFile !== null)             $data['CardOnFile']              = $this->cardOnFile;
        if ($this->accountVerification !== null)    $data['AccountVerification']     = $this->accountVerification;
        if ($this->source !== null)                 $data['Source']                  = $this->source->toArray();
        if ($this->terminalId !== null)             $data['TerminalId']              = $this->terminalId;
        if ($this->terminalCode !== null)           $data['TerminalCode']            = $this->terminalCode;
        if ($this->terminalSerialNumber !== null)   $data['TerminalSerialNumber']    = $this->terminalSerialNumber;
        if ($this->externalIdentifier !== null)     $data['ExternalIdentifier']      = $this->externalIdentifier;
        if ($this->externalBatchIdentifier !== null)$data['ExternalBatchIdentifier'] = $this->externalBatchIdentifier;
        if ($this->externalGroupIdentifier !== null)$data['ExternalGroupIdentifier'] = $this->externalGroupIdentifier;
        if ($this->orderIdentifier !== null)        $data['OrderIdentifier']         = $this->orderIdentifier;
        if ($this->email !== null)                  $data['EmailAddress']            = $this->email;
        if ($this->phoneNumber !== null)            $data['PhoneNumber']             = $this->phoneNumber;
        if ($this->billingAddress !== null)         $data['BillingAddress']          = $this->billingAddress->toArray();
        if ($this->shippingAddress !== null)        $data['ShippingAddress']         = $this->shippingAddress->toArray();
        if ($this->extendedData !== null)           $data['ExtendedData']            = $this->extendedData->toArray();

        return $data;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    private function validateBase(): void
    {
        if ($this->totalAmount <= 0) {
            throw new ValidationException('TotalAmount must be greater than 0.');
        }
        if (!preg_match('/^\d{3}$/', $this->currencyCode)) {
            throw new ValidationException('CurrencyCode must be 3-digit ISO 4217 numeric (e.g. "840" for USD, "388" for JMD).');
        }
    }
}

<?php

declare(strict_types=1);

namespace PowerTranz\Request\Beans;

/**
 * Billing or shipping address fields for gateway requests and parsed responses.
 */
class Address
{
    private ?string $firstName    = null;
    private ?string $lastName     = null;
    private ?string $line1        = null;
    private ?string $line2        = null;
    private ?string $city         = null;
    private ?string $state        = null;
    private ?string $postalCode   = null;
    private ?string $countryCode  = null;
    private ?string $emailAddress = null;
    private ?string $phoneNumber  = null;
    private ?string $company      = null;

    public function setFirstName(string $v): void    { $this->firstName = $v; }
    public function setLastName(string $v): void     { $this->lastName = $v; }
    public function setLine1(string $v): void        { $this->line1 = $v; }
    public function setLine2(string $v): void        { $this->line2 = $v; }
    public function setCity(string $v): void         { $this->city = $v; }
    public function setState(string $v): void        { $this->state = $v; }
    public function setPostalCode(string $v): void   { $this->postalCode = $v; }
    public function setCountryCode(string $v): void  { $this->countryCode = $v; }
    public function setEmailAddress(string $v): void { $this->emailAddress = $v; }
    public function setPhoneNumber(string $v): void  { $this->phoneNumber = $v; }
    public function setCompany(string $v): void      { $this->company = $v; }

    public function getFirstName(): ?string    { return $this->firstName; }
    public function getLastName(): ?string     { return $this->lastName; }
    public function getLine1(): ?string        { return $this->line1; }
    public function getLine2(): ?string        { return $this->line2; }
    public function getCity(): ?string         { return $this->city; }
    public function getState(): ?string        { return $this->state; }
    public function getPostalCode(): ?string   { return $this->postalCode; }
    public function getCountryCode(): ?string  { return $this->countryCode; }
    public function getEmailAddress(): ?string { return $this->emailAddress; }
    public function getPhoneNumber(): ?string  { return $this->phoneNumber; }
    public function getCompany(): ?string      { return $this->company; }

    public function getFullName(): string
    {
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }

    public function toArray(): array
    {
        $data = [];
        if ($this->firstName !== null)    $data['FirstName']    = $this->firstName;
        if ($this->lastName !== null)     $data['LastName']     = $this->lastName;
        if ($this->line1 !== null)        $data['Line1']        = $this->line1;
        if ($this->line2 !== null)        $data['Line2']        = $this->line2;
        if ($this->city !== null)         $data['City']         = $this->city;
        if ($this->state !== null)        $data['State']        = $this->state;
        if ($this->postalCode !== null)   $data['PostalCode']   = $this->postalCode;
        if ($this->countryCode !== null)  $data['CountryCode']  = $this->countryCode;
        if ($this->emailAddress !== null) $data['EmailAddress'] = $this->emailAddress;
        if ($this->phoneNumber !== null)  $data['PhoneNumber']  = $this->phoneNumber;
        if ($this->company !== null)      $data['Company']      = $this->company;
        return $data;
    }

    public static function fromArray(array $data): self
    {
        $addr = new self();
        if (isset($data['FirstName']))    $addr->setFirstName($data['FirstName']);
        if (isset($data['LastName']))     $addr->setLastName($data['LastName']);
        if (isset($data['Line1']))        $addr->setLine1($data['Line1']);
        if (isset($data['Line2']))        $addr->setLine2($data['Line2']);
        if (isset($data['City']))         $addr->setCity($data['City']);
        if (isset($data['State']))        $addr->setState($data['State']);
        if (isset($data['PostalCode']))   $addr->setPostalCode($data['PostalCode']);
        if (isset($data['CountryCode']))  $addr->setCountryCode($data['CountryCode']);
        if (isset($data['EmailAddress'])) $addr->setEmailAddress($data['EmailAddress']);
        if (isset($data['PhoneNumber']))  $addr->setPhoneNumber($data['PhoneNumber']);
        return $addr;
    }
}
